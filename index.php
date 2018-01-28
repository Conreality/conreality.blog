<?php
require_once __DIR__ . '/.php/blog.php';

define('IS_LOCALHOST', strpos($_SERVER['HTTP_HOST'], 'localhost') === 0);

// Enforce HTTPS in production:
if (!IS_LOCALHOST && empty($_SERVER['HTTPS'])) {
  $host = $_SERVER['HTTP_HOST'];
  header("Location: https://$host" . $_SERVER['REQUEST_URI'], true, 301);
  return;
}

// Parse the request path for the page ID:
$matched = preg_match('|^/(\d{4}/\d{2}/[0-9A-Za-z-]+)$|', $_SERVER['REQUEST_URI'], $matches)
        || preg_match('|^/(drafts/[0-9A-Za-z-]+)$|', $_SERVER['REQUEST_URI'], $matches);

$blog = new Blog(__DIR__);
$page = $matched ? $blog->get_post($matches[1]) : $blog->get_page(substr($_SERVER['REQUEST_URI'], 1));

header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $page->get_mtime()) . ' GMT');

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
  <head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <meta name="description" content="Conreality is a live-action augmented-reality, tactical wargame platform."/>
    <meta name="author" content="Conreality.org"/>
    <title><?php echo $page->get_title() ? "{$page->get_title()} &mdash; " : '' ?>Conreality Blog</title>
    <base href="/"/>
    <link rel="icon" href="favicon.ico"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha256-QUyqZrt5vIjBumoqQV0jM8CgGqscFfdGhN+nVCqX0vc=" crossorigin="anonymous"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous"/>
    <link rel="stylesheet" href="index.css"/>
  </head>
  <body role="document">
    <header>
      <nav id="navbar" class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <a class="navbar-brand" href="/">Conreality Blog</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-content"
                aria-controls="navbar-content" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div id="navbar-content" class="collapse navbar-collapse">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item">
              <b><a class="nav-link" href="https://github.com/conreality/blog.conreality.org/commits/master"><?php echo gmdate('Y-m-d', $page->get_mtime()) ?></a></b>
            </li>
          </ul>
          <form class="form-inline my-2 my-lg-0">
            <input id="search-text" class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search"/>
            <button id="search-button" class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
          </form>
        </div>
      </nav>
    </header>

    <main role="main" class="container">
      <div class="row">
        <div id="content" role="article" class="col-md-9">
          <?php echo isset($page->html) ? $page->html : $page->get_html() ?>
        </div>
        <div id="sidebar" role="tree" class="col-md-3">
          <div class="panel panel-default">
            <div class="panel-body">
              <!-- sidebar goes here -->
            </div>
          </div>
        </div>
      </div>
      <hr/>
    </main>

    <footer class="container">
      <p class="float-right">
        <a href="http://wiki.conreality.org/License"><img alt="No copyright" src="https://conreality.org/img/pd.svg" style="height: 1rem;"/></a>
        <a href="http://wiki.conreality.org/License">Public domain</a>
      </p>
      <p role="list">
        <a role="listitem" href="https://conreality.org">Conreality.org</a>
        &ni;
        <a role="listitem" href="https://blog.conreality.org"><b>Blog</b></a>
        &middot;
        <a role="listitem" href="https://wiki.conreality.org">Wiki</a>
        &middot;
        <a role="listitem" href="https://api.conreality.org">API</a>
        &middot;
        <a role="listitem" href="https://gdk.conreality.org">GDK</a>
        &middot;
        <a role="listitem" href="https://sdk.conreality.org">SDK</a>
        &middot;
        <a role="listitem" href="https://ddk.conreality.org">DDK</a>
        &middot;
        <a role="listitem" href="https://git.conreality.org">Code</a>
      </p>
    </footer>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-beta.2/js/bootstrap.bundle.min.js" integrity="sha256-RJDxW82QORKYXHi6Cx1Ku8lPfuwkDIBQaFZ20HGxPXQ=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.9.4/holder.min.js" integrity="sha256-ifihHN6L/pNU1ZQikrAb7CnyMBvisKG3SUAab0F3kVU=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://conreality.org/js/ie10-viewport-bug-workaround.js"></script>
    <script type="text/javascript" src="https://conreality.org/js/search.js"></script>
    <script type="text/javascript" src="index.js"></script>
  </body>
</html>
