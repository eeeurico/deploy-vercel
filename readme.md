# Deploy to Vercel

WordPress plugin to trigger and monitor deployments of a headless front end on [Vercel](https://vercel.com/), and to revalidate single Next.js pages from the post editor.

Available on WordPress.org as [`deploy-vercel`](https://wordpress.org/plugins/deploy-vercel/). See [readme.txt](readme.txt) for the full documentation, FAQ (including an example Next.js revalidation route) and changelog.

## Setup

1. Create a [deploy hook](https://vercel.com/docs/deploy-hooks) for your Vercel project and an [access token](https://vercel.com/account/tokens).
2. In WordPress, go to **Deploy to Vercel → Settings** and paste both. Optionally add the project name and team ID to filter the deployment list.
3. For on-demand revalidation, set the **Revalidation URL** (e.g. `https://example.com/api/revalidate`) and, optionally, a **Revalidation secret** sent in the `x-revalidate-secret` header.

The token, deploy hook and secret stay on the server: the admin UI talks to the plugin's REST routes (`deploy-vercel/v1`), and WordPress calls Vercel.

## Development

No build step. The plugin is plain PHP plus `assets/admin.js` and `assets/admin.css`.

```
deploy-vercel.php                          bootstrap and constants
includes/class-deploy-vercel-settings.php  option, migration, settings page
includes/class-deploy-vercel-api.php       REST routes and remote requests
includes/class-deploy-vercel-admin.php     deployments page, meta box, assets
uninstall.php                              removes the settings
```

## Credits

Inspired by [Vercel Deploy for Strapi](https://market.strapi.io/plugins/strapi-plugin-vercel-deploy).

<a href="https://github.com/eeeurico/deploy-vercel/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=eeeurico/deploy-vercel" />
</a>
