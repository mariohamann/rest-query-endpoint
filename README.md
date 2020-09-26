# Rest Query Endpoint (Experiment)
At the moment the Query Block uses the REST Api endpoints to fetch posts for the backend and WP_Query for frontend. The REST API is currently very limited.
This is an experiment to provide the full power of WP_Query and WP_Term_Query for the Gutenberg Query Block.

## Idea
- The plugin registers a new REST-Endpoint `/wp/v2/query`. (Alternatives considered: wp/gutenberg/v1/query, wp-gutenberg/v1/query, wp-admin/v1/query as described over [here](https://make.wordpress.org/core/2020/01/31/rest-api-introduce-dashboard-namespace/))
- The new endpoint accepts a JSON in the paramater `json` e. g. `https://query-endpoint.mariohamann.de/wp-json/wp/v2/query?json={}`.
- The JSON can contain `query` (= query name as a string) and `args` (= args for Query as array)
- The `args`can contain every paramter as described in [WP Query](https://developer.wordpress.org/reference/classes/wp_query/) and [WP_Term_Query](https://developer.wordpress.org/reference/classes/wp_term_query/) (as JSON istead of PHP args)
- The JSON is converted to a PHP object and the Query is built. The posts of the Query are returned.

## Concept for Query Block
The Query Block would work as a kind of "JSON-Builder". Its only job is to create a well formatted JSON which could be send to the query-Endpoint as parameter. The UI would have to follow the possibilities of WP Query and WP_Term_Query. Frontend and backend recieve exactly the same arguments and the same logic.

## Examples
- Random post: https://query-endpoint.mariohamann.de/wp-json/wp/v2/query?json={"args":{"orderby":"rand","posts_per_page":"1"}}
- Order posts by comments: https://query-endpoint.mariohamann.de/wp-json/wp/v2/query?json={"args":{"orderby":"comment_count","order":"ASC"}}
- Show categories, ordered by count: https://query-endpoint.mariohamann.de/wp-json/wp/v2/query?json={"query":"WP_Term_Query","args":{"taxonomy":"category","orderby":"count","order":"DESC","hide_empty":false}}
- Show pages: https://query-endpoint.mariohamann.de/wp-json/wp/v2/query?json={"args":{"post_type":"page"}}

## Security issues
This query can find EVERY post type and term, as long it is queryable, e. g. `nav_menu` & `page` work, `revision` and `attachment`not. 
- Restrict it to Gutenberg (There could be a check if it is used per HTTP or by Gutenberg Editor, maybe with nonces?)
- Restrict it to users with capabilities to post types (maybe block variants could handle this)