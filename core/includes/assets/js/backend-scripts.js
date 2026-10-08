/*------------------------ 
Backend related javascript
------------------------*/

// Extend the HTMLElement class to create the web component
class VercelDeployApp extends HTMLElement {
  constructor() {
    // Always call super first in constructor
    super()

    // states
    this.state = {
      config: {},
      error: null,
      loading: true,
      deployments: [],
      deploying: false,
    }
    this.divs = {
      content: null,
      actions: null,
    }
  }
  // We'll create our web component here

  /**
   * Runs each time the element is appended to or moved in the DOM
   */
  connectedCallback() {
    // Get the configuration from the data attribute
    const config = this.getAttribute("data-config")
    this.state.config = JSON.parse(config)

    this.innerHTML = this.renderLayout()
    this.divs.content = this.querySelector(".vercel-deploy__content")
    this.divs.actions = this.querySelector(".vercel-deploy__actions")

    this.divs.content.innerHTML = this.renderLoading()

    // if no deploy hook or api token, show error
    if (!this.state.config.deploy_hook || !this.state.config.api_token) {
      this.state.loading = false
      this.divs.content.innerHTML = this.renderMissingConfig()
      return
    }

    // add deploy button
    this.divs.actions.innerHTML = `<button class="button button-primary" id="vercel-deploy-button">Deploy</button>`

    // fetch deployments
    this.getDeployments().then(({ deployments = [] }) => {
      this.state.deployments = deployments
      this.state.loading = false
      this.divs.content.innerHTML = this.renderDeployments()
    })

    this.addEvents()
  }

  /**
   * Runs when the element is removed from the DOM
   */
  disconnectedCallback() {
    console.log("disconnected", this)
  }

  /**
   * Views
   */

  renderLayout() {
    return `
        <div class="vercel-deploy__header">
            <h1 class="vercel-deploy__title">Deploy to Vercel</h1>
            <div class="vercel-deploy__actions"></div>
        </div>
        <div class="vercel-deploy__content"></div>
    `
  }

  renderDeployments() {
    const deployments = this.state.deployments
    return `<div class="table-wrap"><table>
        <thead>
            <tr>
                <th>App Name</th>
                <th>State</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            ${deployments
              .map((deployment) => this.renderDeploymentsRow(deployment))
              .join("")}
        </tbody>
    </table>
    </div>
    `
  }

  renderDeploymentsRow(deployment) {
    return `<tr>
        <td>${deployment.name}</td>
        <td>
            <div class="status status--${deployment.state}">
                ${deployment.state}
            </div>
        </td>
        <td>${this.getDate(deployment.created)}</td>
        <td class="actions">
            <a href="${deployment.url}" target="_blank">
                <span class="dashicons dashicons-admin-links"></span>
            </a>
            <a href="${deployment.inspectorUrl}" target="_blank">
                <span class="dashicons dashicons-search"></span>
            </a>
        </td>
    </tr>`
  }

  renderMissingConfig() {
    return `<div>
        <p>
            <strong>Missing configuration</strong>
            <br>
            Please add the <strong>Deploy Hook</strong> and <strong>Api Token</strong> in the Settings page.
        </p>
    </div>`
  }

  renderLoading() {
    return `<div>Loading...</div>`
  }

  /**
   * Actions
   */

  addEvents() {
    const deployButton = this.querySelector("#vercel-deploy-button")
    deployButton.addEventListener("click", () => {
      this.state.deploying = true
      this.setDeployBtnState()

      this.runDeploy().then(({ data, error }) => {
        if (error) {
          this.state.deploying = false
          this.setDeployBtnState()
          console.error("[vercel-deploy] Error while deploying -", error)
          return
        }

        const deployJobId = data.deployJobId
        if (!deployJobId) {
          console.error("[vercel-deploy] Deployment Id not received")
          this.state.deploying = false
          this.setDeployBtnState()
          return
        }

        console.log(`Deployment started. Job Id: ${deployJobId}`)
        this.startDeploymentPolling(deployJobId)
      })
    })
  }

  setDeployBtnState() {
    const deployButton = this.querySelector("#vercel-deploy-button")
    if (this.state.deploying) {
      deployButton.classList.add("is-loading")
      deployButton.setAttribute("disabled", true)
      deployButton.innerHTML = "Deploying..."
    } else {
      deployButton.classList.remove("is-loading")
      deployButton.removeAttribute("disabled")
      deployButton.innerHTML = "Deploy"
    }
  }

  startDeploymentPolling(deployJobId) {
    const interval = setInterval(() => {
      this.getDeployments().then(({ deployments = [] }) => {
        console.log("polling deployments", deployments)
        this.state.deployments = deployments
        this.divs.content.innerHTML = this.renderDeployments()
        const findBuildingDeployment = deployments.find(
          (deployment) =>
            deployment.state === "BUILDING" || deployment.state === "QUEUED"
        )
        if (!findBuildingDeployment) {
          clearInterval(interval)
          this.state.deploying = false
          this.setDeployBtnState()
        }
      })
    }, 3000)
  }

  async getDeployments() {
    try {
      const config = this.state.config
      if (!config || !config.api_token) {
        throw "missing configuration: api_token"
      }

      const params = {}

      if (config.app_name) {
        params.app = config.app_name
      }

      if (config.team_id) {
        params.teamId = config.team_id
      }

      const response = await fetch(
        "https://api.vercel.com/v6/deployments?" +
          new URLSearchParams(params).toString(),
        {
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${config.api_token}`,
          },
        }
      )
      const data = await response.json()

      return data
    } catch (error) {
      console.error("[vercel-deploy] Error while fetching deployments -", error)
      return {
        error: "An error occurred",
      }
    }
  }

  async runDeploy() {
    try {
      const config = this.state.config
      if (!config || !config.deploy_hook) {
        throw "missing configuration: deploy_hook"
      }

      const response = await fetch(config.deploy_hook, {
        method: "POST",
      })

      const data = await response.json()

      const deployJobId = data?.job?.id
      if (!deployJobId) {
        throw new Error(
          `Deployment Id not received. Response: ${JSON.stringify(response)}`
        )
      }

      return {
        data: {
          deployJobId,
        },
      }
    } catch (error) {
      console.error("[vercel-deploy] Error while deploying -", error)
      return {
        error: "An error occurred",
      }
    }
  }

  /**
   * Helpers
   */
  getDate(date) {
    const d = new Date(date)
    return d.toLocaleString()
  }
}

// Define the new web component
if ("customElements" in window) {
  customElements.define("vercel-deploy-app", VercelDeployApp)
}

/**
 * Add metabox to post type
 */

class VercelDeployMetaBox extends HTMLElement {
  constructor() {
    // Always call super first in constructor
    super()

    // states
    this.state = {
      config: {},
      error: null,
      loading: true,
      revalidating: false,
    }
  }
  // We'll create our web component here

  /**
   * Runs each time the element is appended to or moved in the DOM
   */
  connectedCallback() {
    // Get the configuration from the data attribute
    const config = this.getAttribute("data-config")
    this.state.config = JSON.parse(config)

    const revalidateUrl = `${this.state.config.revalidation_url}?path=${this.state.config.path}&post_type=${this.state.config.post_type}`

    // add deploy button
    // this.innerHTML = `<a href="${revalidateUrl}" class="button button-primary" id="vercel-revalidate-button">Revalidate</a>`
    this.innerHTML = `<button class="button button-primary" id="vercel-revalidate-button" data-url="${revalidateUrl}">Revalidate Page</button>`
    // add events to button
    this.addEvents()
  }

  /**
   * Runs when the element is removed from the DOM
   */
  disconnectedCallback() {
    console.log("disconnected", this)
  }

  /**
   * Actions
   */

  addEvents() {
    const revalidateButton = this.querySelector("#vercel-revalidate-button")
    const revalidateUrl = revalidateButton.getAttribute("data-url")
    if (!revalidateUrl) return console.error("Missing revalidate url")
    revalidateButton.addEventListener("click", () => {
      this.state.revalidating = true
      this.setRevalidationBtnState()

      this.runRevalidation(revalidateUrl).then(({ data, error }) => {
        //  delay
        setTimeout(() => {
          if (error) {
            this.state.revalidating = false
            this.setRevalidationBtnState()
            console.error("[vercel-deploy] Error while deploying -", error)
            return
          }

          this.state.revalidating = false
          this.setRevalidationBtnState()
          console.log(`Revalidated. Response: ${JSON.stringify(data)}`)
        }, 1000)
      })
    })
  }

  setRevalidationBtnState() {
    const revalidateButton = this.querySelector("#vercel-revalidate-button")
    if (this.state.revalidating) {
      revalidateButton.classList.add("is-loading")
      revalidateButton.setAttribute("disabled", true)
      revalidateButton.innerHTML = "Revalidating..."
    } else {
      revalidateButton.classList.remove("is-loading")
      revalidateButton.removeAttribute("disabled")
      revalidateButton.innerHTML = "Revalidate"
    }
  }

  async runRevalidation(revalidateUrl) {
    try {
      if (!revalidateUrl) {
        throw "missing revalidate url"
      }
      const response = await fetch(revalidateUrl, {
        method: "GET",
      })

      const data = await response.json()

      if (data.error) {
        throw new Error(data.error)
      }

      return {
        data,
      }
    } catch (error) {
      console.error("[vercel-deploy-meta-box] Error while deploying -", error)
      return {
        error: "An error occurred",
      }
    }
  }
}

// Define the new web component
if ("customElements" in window) {
  customElements.define("vercel-deploy-meta-box", VercelDeployMetaBox)
}
