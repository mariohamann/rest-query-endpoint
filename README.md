# Rest Query Endpoint (Experiment)

At the moment the Query Block uses the `REST API` endpoints to fetch posts for the backend and `WP_Query` for frontend. The `REST API` is currently very limited.
This is an experiment to provide the full power of `WP_Query` and `WP_Term_Query` for the Gutenberg Query Block.


## Concept for Query Block

The Query Block could work as a kind of "JSON-Builder". Its job is to create a well formatted JSON which could be send to a Query-Endpoint, which uses the recieved parameters as  arguments for  `WP Query` or `WP_Term_Query` and returns the results. With this approach frontend and backend could recieve exactly the same arguments.

![Screen Recording 2020-09-27 at 11 52 41](https://user-images.githubusercontent.com/26542182/94361955-3b12c380-00b8-11eb-9f7f-79b70d11453e.gif)

## What the plugin does

- The plugin registers a new REST-Endpoint `/wp/v2/query` which accepts a POST request with JSON in body.
- The JSON can contain `query` (= query name as a string) and `args` (= args for Query as array)
  
```json
{
    "query":"WP_Query",
    "args":{
        "orderby":"comment_count",
        "order":"ASC"
    }
}
```

- `args` can contain every paramter as described in [WP Query](https://developer.wordpress.org/reference/classes/wp_query/) or [WP_Term_Query](https://developer.wordpress.org/reference/classes/wp_term_query/)
- The arguments are used to build the Query and return the resulting objects.


## Examples

- **Return random post** `{"args":{"orderby":"rand","posts_per_page":"1"}}`: [Demo using params in URL (Refresh to see effect)](https://query-endpoint.mariohamann.de/wp-json/wp/v2/query?json={"args":{"orderby":"rand","posts_per_page":"1"}})
- **Return posts, ordered by comments** `{"args":{"orderby":"comment_count","order":"ASC"}}`: [Demo using params in URL](https://query-endpoint.mariohamann.de/wp-json/wp/v2/query?json={"args":{"orderby":"comment_count","order":"ASC"}})
- **Return categories, ordered by count** `{"query":"WP_Term_Query","args":{"taxonomy":"category","orderby":"count","order":"DESC","hide_empty":false}}`: [Demo using params in URL](https://query-endpoint.mariohamann.de/wp-json/wp/v2/query?json={"query":"WP_Term_Query","args":{"taxonomy":"category","orderby":"count","order":"DESC","hide_empty":false}})
- **Return pages** `{"args":{"post_type":"page"}}`: [Demo using params in URL](https://query-endpoint.mariohamann.de/wp-json/wp/v2/query?json={"args":{"post_type":"page"}})

## Downsides

### Security issues

This query can find EVERY post type and term, as long it is queryable, e. g. `nav_menu` & `page` work, `revision` and `attachment` don't. There are at least two ideas to improve security:

- Restrict the request to Gutenberg editor (saying there could be a check in PHP if the request is send by HTTP or by Gutenberg Editor, maybe with nonces?)
- Restrict it to users with capabilities to requested post types (Example: Someone who can't view a post type couldn't see them in the query block as well?)

## Alternatives considered

### Namespace

- `wp/gutenberg/v1/query`
- `wp-gutenberg/v1/query`
- `wp-admin/v1/query` as described over [here](https://make.wordpress.org/core/2020/01/31/rest-api-introduce-dashboard-namespace/)

### GET request

The first version of the plugin used a GET request, where the whole JSON was in URL as parameter `json`  e. g. `https://query-endpoint.mariohamann.de/wp-json/wp/v2/query?json={}`.. Of course this "sloppy" approach had some downsides.

- We had to encode the JSON request to make the URL work properly.
- The URL is limited to max. 2048 characters according to [W3Schools](https://www.w3schools.com/tags/ref_httpmethods.asp).
- It's strange to send a JSON in a URL instead of using a JSON body.

The functionality is still integrated in the plugin to create the demo URLs.