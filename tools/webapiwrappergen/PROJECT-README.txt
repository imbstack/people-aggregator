To add project-specific API functions, create another .api file in
this directory (start by copying example.api) and add methods, as in
peepagg.api.

build.sh will build that API file as well as peepagg.api and give you
a combined descriptor in web/api/lib/api_desc.php.  Implement the API
methods in web/api/lib/project_api_impl.php, or create another
*_api_impl.php file and include it from project_api_impl.php.
