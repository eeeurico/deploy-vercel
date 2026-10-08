=== Deploy to Vercel ===
Contributors: eeeurico
Tags: vercel, deploy, headless, nextjs, revalidate
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Trigger and monitor Vercel deployments of your headless front end, and revalidate single Next.js pages, from the WordPress admin.

== Description ==

Deploy to Vercel is for sites that use WordPress as a headless CMS with a front end hosted on [Vercel](https://vercel.com/).

* **Deploy:** start a new deployment with one click, using a Vercel deploy hook.
* **Monitor:** see your latest deployments and their state, updated while a deployment is running.
* **Revalidate:** a "Revalidate page" button on posts and pages asks your Next.js site to refresh that page (on-demand revalidation), without a full deployment.

Your Vercel API token, deploy hook and revalidation secret are stored on your server and are only used by WordPress itself. They are never sent to the browser.

This plugin is not affiliated with or endorsed by Vercel Inc.

Inspired by [Vercel Deploy for Strapi](https://market.strapi.io/plugins/strapi-plugin-vercel-deploy).

== Installation ==

1. Install and activate the plugin from **Plugins → Add New**, or upload the `deploy-vercel` folder to `/wp-content/plugins/`.
2. In Vercel, create a [deploy hook](https://vercel.com/docs/deploy-hooks) for your project and an [access token](https://vercel.com/account/tokens).
3. Go to **Deploy to Vercel → Settings**, paste the deploy hook URL and the token, and save.
4. Optionally fill in the project name and team ID to only list that project's deployments.
5. Go to **Deploy to Vercel** and click **Deploy**.

Only administrators (users who can manage options) can see deployments and start them. Anyone who can edit a post can revalidate it.

== Frequently Asked Questions ==

= Which settings do I need? =

The deploy hook URL and an API token are required for the deployments page. Project name and team ID are optional filters. The revalidation URL is only needed for the "Revalidate page" button.

= How do I find my team ID? =

Open your team in the Vercel dashboard and go to **Settings → General**. You can use the team ID or the team slug.

= How does revalidation work? =

When an editor clicks **Revalidate page**, WordPress sends a `GET` request to your revalidation URL with two query parameters:

* `path`: the path of the post, for example `/about/team/`
* `post_type`: the post type, for example `page`

If you set a revalidation secret, it is sent in the `x-revalidate-secret` header. Only published content can be revalidated.

= Can you show an example Next.js route? =

For the App Router, create `app/api/revalidate/route.js`:

`
import { revalidatePath } from "next/cache"

export async function GET(request) {
  if (request.headers.get("x-revalidate-secret") !== process.env.REVALIDATE_SECRET) {
    return Response.json({ message: "Invalid secret" }, { status: 401 })
  }

  const path = request.nextUrl.searchParams.get("path")
  if (!path) {
    return Response.json({ message: "Missing path" }, { status: 400 })
  }

  revalidatePath(path)
  return Response.json({ revalidated: true, path })
}
`

Set `REVALIDATE_SECRET` in your Vercel project to the same value as the plugin's "Revalidation secret" setting, then use `https://your-site.com/api/revalidate` as the revalidation URL.

= Can I show the revalidate box on custom post types? =

Yes, with the `deploy_vercel_meta_box_post_types` filter:

`
add_filter( 'deploy_vercel_meta_box_post_types', function ( $post_types ) {
	$post_types[] = 'product';
	return $post_types;
} );
`

= Where is the source code? =

On [GitHub](https://github.com/eeeurico/deploy-vercel). Issues and pull requests are welcome.

== External services ==

This plugin connects to the following services. Nothing is sent until an administrator saves the settings, and then only when the actions below happen.

= Vercel REST API =

Used to list the latest deployments of your Vercel account or team.

* **What is sent:** your API token (as an `Authorization` header) and, if set, the project name and team ID.
* **When:** when an administrator opens the **Deploy to Vercel** page, and every few seconds while a deployment started from that page is running.
* **Endpoint:** `https://api.vercel.com/v6/deployments`

= Vercel deploy hook =

Used to start a new deployment of your project.

* **What is sent:** an empty `POST` request to the deploy hook URL you entered (`https://api.vercel.com/v1/integrations/deploy/...`).
* **When:** only when an administrator clicks **Deploy**.

Both services are provided by Vercel Inc.: [Terms of Service](https://vercel.com/legal/terms), [Privacy Policy](https://vercel.com/legal/privacy-policy).

= Your site's revalidation URL =

Used for on-demand revalidation of your own front end, at the URL you entered in the settings. This is usually your own Next.js site; if it is hosted on Vercel, the Vercel terms and privacy policy above apply.

* **What is sent:** the path and post type of the post, and the revalidation secret (as an `x-revalidate-secret` header) if you set one.
* **When:** only when a user clicks **Revalidate page** on a published post.

== Changelog ==

= 1.1.0 =
* Security: the API token and deploy hook are no longer sent to the browser. All requests to Vercel are made by WordPress through new REST endpoints.
* Security: secret settings are shown as password fields and are never displayed again after saving.
* New: optional revalidation secret, sent in the `x-revalidate-secret` header.
* New: "Settings" link on the Plugins screen.
* Improved: the revalidation path is worked out on the server from the post, and only published content can be revalidated.
* Improved: after clicking Deploy, the page waits for the new deployment to appear and finish.
* Improved: scripts and styles only load on the plugin page and on edit screens that show the revalidate box.
* Improved: the admin interface is translatable, including the JavaScript.
* Changed: settings are stored in the `deploy_vercel_settings` option. Settings from 1.0.x are migrated automatically.
* Changed: the meta box filter `VDWP/meta_box/post_types` is now `deploy_vercel_meta_box_post_types`.
* Requires WordPress 6.0 and PHP 7.4.

= 1.0.4 =
* Add "Revalidate" meta box to posts and pages for on-demand revalidation of a Next.js front end.
* Add Revalidation URL setting.

= 1.0.3 =
* Changes recommended by the WordPress Plugin Directory team.

= 1.0.2 =
* Rename the plugin as recommended by the WordPress Plugin Directory team.

= 1.0.0 =
* First release.

== Upgrade Notice ==

= 1.1.0 =
Security update: your Vercel token and deploy hook are no longer exposed in the browser. Settings are migrated automatically. If your revalidation endpoint should only accept WordPress, set a revalidation secret.
