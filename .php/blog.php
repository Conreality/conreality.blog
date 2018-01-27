<?php
define('BLOG_FILE_EXT', '.rst'); // reST
define('BLOG_RST2HTML', __DIR__ . '/../.python/bin/rst2html5.py');
//define('BLOG_RST2HTML', '/opt/homebrew/bin/rst2html5.py');

class Blog {
  function __construct($path, $suffix = BLOG_FILE_EXT) {
    $this->path = $path;
    $this->suffix = $suffix;
  }

  function get_path($path) {
    return $this->path . '/' . $path;
  }

  function get_home() {
    return new BlogHomePage($this);
  }

  function get_page($id) {
    if ($id == '') return new BlogHomePage($this);
    return new BlogErrorPage($this);
  }

  function get_post($id) {
    return new BlogPost($this, $id);
  }

  function get_posts($limit = 100) {
    $result = [];
    foreach (range((int)gmdate('Y'), 2017, -1) as $year) {
      foreach (range(12, 1, -1) as $month) {
        $dirname = sprintf("%04d/%02d", $year, $month);
        foreach (glob($this->get_path("{$dirname}/*{$this->suffix}")) as $pathname) {
          $basename = basename($pathname);
          $filename = "{$dirname}/{$basename}";
          if ($basename[0] == '.' || is_link($pathname)) continue; // skip special files and links
          $post_id = str_replace($this->suffix, '', $filename);
          $result[] = new BlogPost($this, $post_id);
        }
      }
    }
    return $result;
  }
}

class BlogPage {
  function __construct($parent, $id) {
    $this->parent = $parent;
    $this->id = $id;
    $this->title = str_replace('-', ' ', $id);
  }

  function exists() {
    return true;
  }

  function is_link() {
    return false;
  }

  function is_virtual() {
    return true;
  }

  function get_mtime() {
    return time();
  }

  function get_title() {
    return $this->title;
  }

  function get_html() {
    return BlogParser::render($this);
  }

  function get_body() {
    return !empty($this->body) ? $this->body : '';
  }
}

class BlogPost extends BlogPage {
  function __construct($parent, $id) {
    parent::__construct($parent, $id);
  }

  function exists() {
    return file_exists($this->get_pathname());
  }

  function is_link() {
    return is_link($this->get_pathname());
  }

  function is_virtual() {
    return false;
  }

  function get_link_target() {
    return str_replace($this->parent->suffix, '', readlink($this->get_pathname()));
  }

  function get_mtime() {
    return filemtime($this->get_pathname());
  }

  function get_title() {
    $lines = file($this->get_pathname());
    return !empty($lines[1]) ? str_replace("\n", '', $lines[1]) : null;
  }

  function get_body() {
    return file_get_contents($this->get_pathname());
  }

  function get_filename() {
    return $this->id . $this->parent->suffix;
  }

  function get_pathname() {
    return $this->parent->get_path($this->get_filename());
  }
}

class BlogHomePage extends BlogPage {
  function __construct($parent) {
    parent::__construct($parent, '');
  }

  function get_html() {
    $result = [];
    foreach ($this->parent->get_posts(5) as $page) {
      $result[] = $page->get_html();
    }
    return implode("\n", $result);
  }
}

class BlogErrorPage extends BlogPage {
  function __construct($parent, $status = 404) {
    parent::__construct($parent, (string)$status);
    $this->title = '404 Not Found';
    $this->status = $status;
  }

  function exists() {
    return false;
  }

  function get_html() {
    return '<h1>' . $this->get_title() . '</h1>';
  }
}

class BlogParser {
  static function render($page) {
    if ($page instanceof BlogErrorPage) return '';
    $command = [];
    $command[] = escapeshellcmd(BLOG_RST2HTML);
    $command[] = escapeshellarg('--template=' . __DIR__ . '/../.rst2html/template.txt');
    $command[] = escapeshellarg('--no-doc-title');
    $command[] = escapeshellarg('--initial-header-level=1');
    $command[] = escapeshellarg($page->get_pathname());
    $command = implode(' ', $command);
    $output = shell_exec($command);
    $output = BlogParser::link_title($output, $page->id);
    return $output;
  }

  static function link_title($input, $page_id) {
    return preg_replace_callback(
      '|<h1>([^<]+)</h1>|',
      function ($matches) use ($page_id) {
        $title = $matches[1];
        return "<a href=\"{$page_id}\"><h1>{$title}</h1></a>";
      },
      $input);
  }
}
