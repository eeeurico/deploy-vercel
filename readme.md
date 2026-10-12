# Headless Deploy Panel for Vercel

Formerly "Deploy to Vercel".

WordPress plugin to trigger and monitor deployments of a headless front end on [Vercel](https://vercel.com/), and to revalidate single Next.js pages from the post editor.

Available on WordPress.org as [`headless-deploy-panel-for-vercel`](https://wordpress.org/plugins/headless-deploy-panel-for-vercel/) (pending review). See [readme.txt](readme.txt) for the full documentation, FAQ (including an example Next.js revalidation route) and changelog.

## Setup

1. Create a [deploy hook](https://vercel.com/docs/deploy-hooks) for your Vercel project and an [access token](https://vercel.com/account/tokens).
2. In WordPress, go to **Deploy Panel → Settings** and paste both. Optionally add the project name and team ID to filter the deployment list.
3. For on-demand revalidation, set the **Revalidation URL** (e.g. `https://example.com/api/revalidate`) and, optionally, a **Revalidation secret** sent in the `x-revalidate-secret` header.

The token, deploy hook and secret stay on the server: the admin UI talks to the plugin's REST routes (`headless-deploy-panel/v1`), and WordPress calls Vercel.

## Development

No build step. The plugin is plain PHP plus `assets/admin.js` and `assets/admin.css`.

```
headless-deploy-panel-for-vercel.php               bootstrap and constants
includes/class-headless-deploy-panel-settings.php  option, migration, settings page
includes/class-headless-deploy-panel-api.php       REST routes and remote requests
includes/class-headless-deploy-panel-admin.php     deploy panel page, meta box, assets
uninstall.php                                      removes the settings
```

## Credits

Inspired by [Vercel Deploy for Strapi](https://market.strapi.io/plugins/strapi-plugin-vercel-deploy).

<a href="https://github.com/eeeurico/headless-deploy-panel-for-vercel/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=eeeurico/headless-deploy-panel-for-vercel" />
</a>
