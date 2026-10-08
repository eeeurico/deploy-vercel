/**
 * Deploy to Vercel admin UI.
 *
 * All Vercel requests go through the plugin's REST routes; this file never
 * sees the API token or the deploy hook.
 */
;(function () {
  "use strict"

  const { __, sprintf } = wp.i18n
  const apiFetch = wp.apiFetch

  const PENDING_STATES = ["QUEUED", "INITIALIZING", "BUILDING"]
  const KNOWN_STATES = PENDING_STATES.concat(["READY", "ERROR", "CANCELED"])
  const POLL_INTERVAL = 5000
  // How long to wait for the deployment started by the hook to show up.
  const APPEAR_TIMEOUT = 3 * 60 * 1000
  // Stop polling a deployment that is still building after this long.
  const BUILD_TIMEOUT = 30 * 60 * 1000

  /**
   * Create an element with optional class name and text.
   */
  function el(tag, className, text) {
    const node = document.createElement(tag)
    if (className) node.className = className
    if (text !== undefined) node.textContent = text
    return node
  }

  /**
   * Return the URL if it is an https link, otherwise null.
   */
  function safeUrl(url) {
    try {
      const parsed = new URL(url)
      return parsed.protocol === "https:" ? parsed.href : null
    } catch (e) {
      return null
    }
  }

  function iconLink(url, icon, label) {
    const href = safeUrl(url)
    if (!href) return null
    const link = el("a")
    link.href = href
    link.target = "_blank"
    link.rel = "noopener noreferrer"
    link.title = label
    link.appendChild(el("span", "dashicons " + icon))
    link.appendChild(el("span", "screen-reader-text", label))
    return link
  }

  function showNotice(container, type, message) {
    container.replaceChildren()
    if (!message) return
    const notice = el("div", "notice inline notice-" + type)
    notice.appendChild(el("p", "", message))
    container.appendChild(notice)
  }

  /**
   * Deployments page.
   */
  function initDeploymentsPage(root) {
    const button = root.querySelector(".deploy-vercel__deploy")
    const list = root.querySelector(".deploy-vercel__deployments")
    const notice = root.querySelector(".deploy-vercel__notice")
    if (!button || !list || !notice) return

    function renderDeployments(deployments) {
      if (!deployments.length) {
        list.replaceChildren(el("p", "", __("No deployments found.", "deploy-vercel")))
        return
      }

      const table = el("table", "widefat striped deploy-vercel__table")
      const headRow = el("tr")
      ;[
        __("Project", "deploy-vercel"),
        __("State", "deploy-vercel"),
        __("Created", "deploy-vercel"),
        __("Links", "deploy-vercel"),
      ].forEach((label) => headRow.appendChild(el("th", "", label)))
      table.appendChild(el("thead")).appendChild(headRow)

      const body = el("tbody")
      deployments.forEach((deployment) => {
        const row = el("tr")
        row.appendChild(el("td", "", deployment.name))

        const state = KNOWN_STATES.includes(deployment.state) ? deployment.state : "UNKNOWN"
        const stateCell = el("td")
        stateCell.appendChild(
          el("span", "deploy-vercel__state deploy-vercel__state--" + state.toLowerCase(), deployment.state || "—")
        )
        row.appendChild(stateCell)

        row.appendChild(
          el("td", "", deployment.created ? new Date(deployment.created).toLocaleString() : "—")
        )

        const links = el("td", "deploy-vercel__links")
        ;[
          iconLink(deployment.url, "dashicons-admin-links", __("Open deployment", "deploy-vercel")),
          iconLink(deployment.inspector_url, "dashicons-search", __("Open in Vercel", "deploy-vercel")),
        ].forEach((link) => link && links.appendChild(link))
        row.appendChild(links)

        body.appendChild(row)
      })
      table.appendChild(body)

      list.replaceChildren(table)
    }

    function loadDeployments() {
      return apiFetch({ path: "/deploy-vercel/v1/deployments" }).then((data) => {
        const deployments = Array.isArray(data.deployments) ? data.deployments : []
        renderDeployments(deployments)
        return deployments
      })
    }

    function setDeploying(deploying) {
      button.disabled = deploying
      button.classList.toggle("is-busy", deploying)
      button.textContent = deploying ? __("Deploying…", "deploy-vercel") : __("Deploy", "deploy-vercel")
    }

    /**
     * Poll until the deployment started at `since` appears and finishes.
     */
    function waitForDeployment(since) {
      const startedAt = Date.now()

      function finish(type, message) {
        showNotice(notice, type, message)
        setDeploying(false)
      }

      function poll() {
        loadDeployments()
          .then((deployments) => {
            const elapsed = Date.now() - startedAt
            const deployment = deployments.find((item) => item.created >= since)

            if (!deployment) {
              if (elapsed > APPEAR_TIMEOUT) {
                finish("warning", __("The deployment has not appeared yet. Vercel may still be queueing it; reload this page later.", "deploy-vercel"))
                return
              }
              showNotice(notice, "info", __("Deployment triggered. Waiting for Vercel to start it…", "deploy-vercel"))
              setTimeout(poll, POLL_INTERVAL)
              return
            }

            if (PENDING_STATES.includes(deployment.state)) {
              if (elapsed > BUILD_TIMEOUT) {
                finish("warning", __("The deployment is taking a long time. Reload this page to check on it.", "deploy-vercel"))
                return
              }
              /* translators: %s: deployment state, e.g. BUILDING. */
              showNotice(notice, "info", sprintf(__("Deployment in progress (%s)…", "deploy-vercel"), deployment.state))
              setTimeout(poll, POLL_INTERVAL)
              return
            }

            if (deployment.state === "READY") {
              finish("success", __("Deployment finished.", "deploy-vercel"))
            } else {
              /* translators: %s: deployment state, e.g. ERROR. */
              finish("error", sprintf(__("Deployment ended with state %s.", "deploy-vercel"), deployment.state))
            }
          })
          .catch((error) => finish("error", error.message))
      }

      poll()
    }

    button.addEventListener("click", () => {
      setDeploying(true)
      showNotice(notice, "info", __("Triggering deployment…", "deploy-vercel"))

      apiFetch({ path: "/deploy-vercel/v1/deploy", method: "POST" })
        .then((job) => waitForDeployment(job.created_at || Date.now()))
        .catch((error) => {
          showNotice(notice, "error", error.message)
          setDeploying(false)
        })
    })

    loadDeployments()
      .then(() => {
        button.disabled = false
      })
      .catch((error) => {
        list.replaceChildren()
        showNotice(notice, "error", error.message)
        button.disabled = false
      })
  }

  /**
   * "Revalidate" meta box on edit screens.
   */
  function initRevalidateBox(box) {
    const button = box.querySelector(".deploy-vercel-revalidate__button")
    const status = box.querySelector(".deploy-vercel-revalidate__status")
    const postId = parseInt(box.dataset.postId, 10)
    if (!button || !status || !postId) return

    button.addEventListener("click", () => {
      button.disabled = true
      button.classList.add("is-busy")
      status.className = "deploy-vercel-revalidate__status"
      status.textContent = __("Revalidating…", "deploy-vercel")

      apiFetch({
        path: "/deploy-vercel/v1/revalidate",
        method: "POST",
        data: { post_id: postId },
      })
        .then((result) => {
          status.classList.add("is-success")
          /* translators: %s: path of the page, e.g. /about/. */
          status.textContent = sprintf(__("Revalidated %s", "deploy-vercel"), result.path)
        })
        .catch((error) => {
          status.classList.add("is-error")
          status.textContent = error.message
        })
        .finally(() => {
          button.disabled = false
          button.classList.remove("is-busy")
        })
    })
  }

  function init() {
    document.querySelectorAll(".deploy-vercel").forEach(initDeploymentsPage)
    document.querySelectorAll(".deploy-vercel-revalidate").forEach(initRevalidateBox)
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init)
  } else {
    init()
  }
})()
