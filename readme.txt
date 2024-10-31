=== MySQL query cache stats ===
Contributors: morisdov
Donate link: http://www.childrensheartlink.org/donate
Tags: mariadb, database, performance, tuning, wp_options
Requires at least: 3.6
Tested up to: 6.4
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2

Admin dashboard widget measuring MySQL database performance & query cache

== Description ==
Admin dashboard widget measuring and monitoring MySQL / MariaDB [query cache](https://mariadb.com/resources/blog/flexible-mariadb-server-query-cache  "MariaDB Query Cache") performance & statistics - to optimize sizing configuration and highlight bottlenecks.

= Why =
Database Query for [Options Autoload](https://kinsta.com/knowledgebase/wp-options-autoloaded-data/#what-is-the-wp_options-table "What is the wp_options table") is repeatedly executed for each WP page load and its poor performance has been identified as a good indicator for a poorly performing database resulting in slow site.

= How =
* Use dashboard widget to measure and monitor `Options Autoload Query Time`
* Use dashboard widget to measure and monitor `Options Autoload Query Size`
This query result size increases constantly over time, as WP site usage grows - but its oversized sudden growth or constant balooning must not be overlooked.

Heavy plugins might rely heavily on adding data to `Options Autoload Query Size`.
Poorly written plugins causing WP performance degradation have been found to constantly increase this query result size.

= Time Metrics =

'Options Autoload Query Time' is measured in milliseconds (ms)

 0 ms - great performance below one millisecond
 1-2 ms - good performance
 3-5 ms - OK performance
 50+ ms - poor performance should be investigated

= Size Metrics =

'Options Autoload Size' is measured in Bytes
If you have the control to enable the database server wide query cache - configure query_cache_limit to be larger than `Options Autoload Size`

 20,000 Bytes - freshly installed clean wordpress site
 30,000 Bytes - small wordpress site
 50,000 Bytes - medium wordpress site
 250,000 Bytes - large wordpress site
 2,000,000+ Bytes - too large should be investigated
( `all above are rough metrics` )

== Frequently Asked Questions ==

= This plugin improves page load performance ? =

No, This plugin in itself does not improve performance by being installed, this plugin only measures time and size of database query.

= This plugin improves database performance ? =

No, This plugin in itself does not improve performance by being installed, an administrator should use the dashboard widget to measure, monitor and identify problems.

= Must database query cache be enabled ? =

No, This plugin measures time and size of a very important database query and these measurements should be valuable to an administrator whether database query cache is enabled or not.



== Screenshots ==
1. Active query cache stats
2. Disabled query cache stats
3. Execute database command RESET QUERY CACHE

== Changelog ==
= 1.0.4 =
* Added wp_options Autoload Query Time
= 1.0.3 =
* Added wp_options Autoload Size
= 1.0.2 =
* Added Database Size
* Added Refresh button
= 1.0.1 =
* Added Questions, removed Queries
Query cache does not cache statements executed within stored programs.
Queries differs from Questions in that it also counts statements executed within stored programs.
Questions differs from Queries in that it doesn't count statements executed within stored programs. 
* Added widget button RESET QUERY CACHE
Execute database command RESET QUERY CACHE.
= 1.0 =
* First release
