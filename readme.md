# Vercel Deploy WP

Wordpress plugin to trigger and monitor a deployment on [Vercel](https://vercel.com/).

Inspired by [Vercel Deploy for Strapi](https://market.strapi.io/plugins/strapi-plugin-vercel-deploy).

## Installation

1. Go to `Plugins` in the Admin menu
2. Click on the button `Add new`
3. Search for `Vercel Deploy WP` and click 'Install Now' or click on the `upload` link to upload `vercel-deploy-wp.zip`
4. Click on `Activate plugin`

## Configuration

To enable the plugin, you will need to create a [Deploy Hook](https://vercel.com/docs/more/deploy-hooks) for your Vercel Project and [API Token](https://vercel.com/account/tokens) for your Vercel Account.

### Settings

After you've created your deploy hook and account token, navigate to `Vercel Deploy -> Settings` in the WordPress admin menu and paste your Vercel Deploy hook URL and account token.
To filter the deployments of your account by Vercel Project fill the `App Name` with the slug of the project and to filter by Team or Account fill the `Team ID` with the slug of the team or account. These values can be used in combination.

## Contributors & Credits

This plugin was based on the [Vercel Deploy for Strapi](https://market.strapi.io/plugins/strapi-plugin-vercel-deploy).

<a href="https://github.com/eeeurico/vercel-deploy-wp/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=eeeurico/vercel-deploy-wp" />
</a>
