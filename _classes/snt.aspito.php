<?php

/**
 * ASPITO. ASsets PIpeline (TOtal)
 * Carlos Cabo 2013
 * GitHub
 */

class Aspito {

  //VALORES POR DEFECTO
  private $data = array(
    // Version
    'v' => '0.2beta 06/2013',

    // Filenames
    'fn' => array(
      'css' => array(
        'filelist' => '_css.cache.config.css',
        'gz'       => '_css.compiled.css.gz',
        'min'      => '_css.compiled.min.css',
        'bulk'     => '_css.compiled.bulk.css'
      ),
      'js' => array(
        'filelist'  => '_js.cache.config',
        'gz'        => '_js.cache.js.gz',
        'min'       => '_js.cache.min.js',
        'bulk'      => '_js.cache.bulk.js'
      )
    ),

    // Paths an command-line to node
    'node' => array(
      'exe'   => 'C:\nodejs\node.exe',
      'ugli'  => 'C:\Users\carlos\AppData\Roaming\npm\node_modules\uglify-js\bin\uglifyjs',
      'sass'  => 'C:\Users\carlos\node_modules\node-sass\bin\node-sass'
    ),

    // Localhost
    'is_local_host' => false,
    'lh_sub'        => 'lh',
    'lh_ext'        => 'dev',

    // Determine if we are working with CSS or JS
    'is'   => false, // What kind of files are? CS / JS

    // Needs SASS
    'needs_sass' => false,

    // Buffer
    'b'     => '', // Buffer
    'bh'    => '', // Buffer header
    'filelist'      => array(),

    // 'Foced' modes
    'forceGZ'   => false, // Force to serve gzip
    'forceMIN'  => false, // Force to serve ugli
    'forceBULK' => false, // Force to serve bulk
    'forcePROD' => false, // Force production
    'forceDEBUG'=> false, // Force debug

    // Headers
    'h' => array(
      'css'   => 'Content-type:  text/css; charset=utf-8',
      'js'    => 'Content-type: application/javascript; charset=utf-8',
      'utf8'    => '@charset "UTF-8";',
      'gz'      => 'Content-Encoding: gzip'
    )
  );

  //CONSTRUCTOR
  function __construct($argumento = null) {

    $p = PHP_EOL;

    $this->detect_localhost();

    $this->get_compile_flags();

    $this->detect_js_or_css();

    // Force production return .gz if possible
    if ($this->data['forcePROD']) {
      $this->force_prod($this->data['is']);
    }

    // header
    $h  = '/*' . $p;
    $h .= '   ======================================' . $p;
    $h .= '   Compiled with ASPITO v.' . $this->data['v'] . $p;
    $h .= '   by Carlos Cabo' . $p;
    $h .= '   https://github.com/carloscabo/aspito'. $p;
    $h .= '   ======================================' . $p;
    $h .= '*/' . $p . $p;
    $this->data['h']['git'] = $h;

    $this->data['h']['date'] = date('Y/m/d H:i:s');

    $this->read_filelist();

    $this->read_files_to_buffer();

    if ($this->data['is'] == 'css') {
      $this->data['h']['git'] = $this->data['h']['utf8'] . $p . $p .  $this->data['h']['git'];
    }

    // Write bulk
    $this->write_to_file (
      $this->data['fn'][$this->data['is']]['bulk'],
      $this->data['h']['git'] . '/* PRODUCTION ' . $this->data['h']['date'] . ' */' . $p . $p . $this->data['bh'] . $this->data['b']
    );

    // Write min
    $min = $this->minify();
    $this->write_to_file (
      $this->data['fn'][$this->data['is']]['min'],
      $this->data['h']['git'] . '/* PRODUCTION ' . $this->data['h']['date'] . ' */' . $p . $p . $this->data['bh'] . $min
    );

    // Write min -> gz
    $this->write_to_file_gz (
      $this->data['fn'][$this->data['is']]['gz'],
      $this->data['h']['git'] . '/* PRODUCTION ' . $this->data['h']['date'] . ' */' . $p . $p . $this->data['bh'] . $min
    );

    // Normal behavior, return bulk
    if (!$this->data['forceGZ'] && !$this->data['forceMIN'] && !$this->data['forceBULK']) {
      header($this->data['h'][$this->data['is']]);
      echo $this->data['h']['git'];
      echo '/* DEVELOPMENT ' .$this->data['h']['date'] . ' */';
      echo $p . $p;
      echo $this->data['bh'] . $this->data['b'];
      die;
    }

    if ($this->data['forceGZ']) {
      header($this->data['h'][$this->data['is']]);
      header($this->data['h']['gz']);
      if (ob_get_contents()) { ob_end_clean(); }
      flush();
      readfile($this->data['fn'][$this->data['is']]['gz']);
      exit;
      die;
    }
    if ($this->data['forceMIN']) {
      header($this->data['h'][$this->data['is']]);
      if (ob_get_contents()) { ob_end_clean(); }
      flush();
      readfile($this->data['fn'][$this->data['is']]['min']);
      exit;
      die;
    }
    if ($this->data['forceBULK']) {
      header($this->data['h'][$this->data['is']]);
      if (ob_get_contents()) { ob_end_clean(); }
      flush();
      readfile($this->data['fn'][$this->data['is']]['bulk']);
      exit;
      die;
    }
    echo 'ERROR 01: SOMETHING WENT WRONG!';
    die;

  } // Construct ---------------------------------

  // Domain that begin with lh or ends with .dev.
  function detect_localhost () {
    $parsedUrl = parse_url('http://' . $_SERVER['SERVER_NAME']);
    $host = explode('.', $parsedUrl['host']);
    if ($host[0] == $this->data['lh_sub'] || end($host) == $this->data['lh_ext'] || in_array($_SERVER['REMOTE_ADDR'], array("127.0.0.1","::1"))) {
      $this->data['is_local_host'] = true;
      require $_SERVER['DOCUMENT_ROOT'] . '/_classes/lib/debuglib.php';
    }
  }

  function detect_js_or_css() {
    if (file_exists($this->data['fn']['css']['filelist'])) {
      $this->data['is'] = 'css';
    } else if (file_exists($this->data['fn']['js']['filelist'])) {
      $this->data['is'] = 'js';
    } else {
      echo 'ERROR 02: DID NOT FIND ANY CONFIG FILE HERE!';
      die;
    }
  }

  function get_compile_flags () {
    // Flags only in localhost
    if ($this->data['is_local_host']) {
      // Detect zlib
      if(!extension_loaded('zlib')){
        echo 'ERROR 03: THERE IS NO ZLIB!';
        die;
      }

      $p = PHP_EOL;
      if (isset($_GET['GZ'])) {
        $this->data['bh'] .= '/* =-= Force GZ =-= */' . $p;
        $this->data['forceGZ'] = true;
      }
      if (isset($_GET['MIN'])) {
        $this->data['bh'] .= '/* =-= Force MIN =-= */' . $p;
        $this->data['forceMIN'] = true;
      }
      if (isset($_GET['BULK'])) {
        $this->data['bh'] .= '/* =-= Force BULK =-= */' . $p;
        $this->data['forceBULK'] = true;
      }
      if (isset($_GET['PROD'])) {
        $this->data['forcePROD'] = true;
      }
      if (isset($_GET['DEBUG'])) {
        $this->data['forceDEBUG'] = true;
      }
    }
  }

  // Get filenames to be included
  function read_filelist() {
    $data = $this->read_file($this->data['fn'][$this->data['is']]['filelist']);

    foreach(preg_split("/(\r?\n)/", $data) as $line){
      if (!empty($line) && in_array($line[0], array('@', '+'))) {
        $this->data['filelist'][] = $this->guess_filename($line);
      }
    }
    $data = null;
    array_filter($this->data['filelist']); //remove empty
  }

  // Tries to extract the filename from the string
  function guess_filename($fn) {
    $ini = strpos ($fn, '"');
    if ($ini > -1) {
      $fn = trim(substr($fn, $ini+1, strrpos($fn, '"')-$ini-1));
      return $fn;
    }
    $ini = strpos ($fn, "'");
    if ($ini > -1) {
      $fn = trim(substr($fn, $ini+1, strrpos($fn, "'")-$ini-1));
      return $fn;
    }
    $fn = trim(substr($fn, 1, 256));
    return $fn;
  }

  // Add the files' content to the buffer
  // Add filenames on top
  function read_files_to_buffer() {
    foreach ($this->data['filelist'] as $f) {
      if (file_exists($f)) {

        // Create header filelist
        // Check if needs SASS
        $this->data['bh'] .= '/* ' . $f;
        if (strpos($f, '.scss') > -1) {
          $this->data['bh'] .= ' -> COMPILED';
          $this->data['needs_sass'] = true;
        }
        $this->data['bh'] .= ' */' . PHP_EOL;

        // Read the file content
        // Add it to the buffer
        $data = file_get_contents($f);
        $this->data['b'] .= PHP_EOL . PHP_EOL . '/* =========================== */' . PHP_EOL . '/* ' . $f . ' */' . PHP_EOL . '/* =========================== */' . PHP_EOL . PHP_EOL . $data;
        $data = null;
      } else {
        echo 'ERROR 04: FILE NOT FOUND -> ' . $f;
        die;
      }
    }
    $this->data['bh'] .= PHP_EOL;

    // CSS Only tasks
    if ($this->data['is'] == 'css') {
      $this->data['b'] = str_ireplace ($this->data['h']['utf8'], '', $this->data['b']);
      // Needs sass? Compile the file!
      if ($this->data['needs_sass']) {
        $this->compile_sass();
      }
    }
  } // read_files_to_buffer ---------------------------

  function compile_sass() {

    // Temp files
    $ts = '____t.scss';
    $t_ = '____t.css';

    // Delete old files
    if (file_exists($ts)) { unlink($ts); }
    if (file_exists($t_)) { unlink($t_); }

    // Write data
    $this->write_to_file ($ts, $this->data['b']);

    // Node-sass command
    $cmd  = '"' . $this->data['node']['exe'] . '" ';
    $cmd .= '"' . $this->data['node']['sass'] . '" ';
    $cmd .= $ts . ' ' . $t_ ; //. ' 2>&1'
    exec($cmd, $output);

    if ($this->data['forceDEBUG'] || !file_exists($t_)) {
      $this->cssError($output);
      die;
    }

    // Rewad resulting file
    $this->data['b'] = file_get_contents($t_);

    if (file_exists($ts)) { unlink($ts); }
    if (file_exists($t_)) { unlink($t_); }
  }

  function cssError ($msg) {
    $msg = implode($msg, " \A ");
    $msg = str_replace(array('[31m','[32m','[33m','[39m', '\n"'), '', $msg);
    // echo $msg ;
    // die;

    header($this->data['h']['css']);
echo <<<EOD
body {
  margin:0;padding:0;
}

body:after {
  content: '$msg';
  display:block;
  white-space: pre-wrap;
  /*width:350px;*/
  padding:20px;
  position:fixed;
  top:0;
  left:0;
  z-index:9999;
  background:#9d0038;

  font-family:"Courier New", Courier, monospace;
  font-size:16px;
  color:#fff;
}
EOD;
die;
  }

  function force_prod ($type) {
    // We are in production server.
    // Or forced with PROD flag.
    // Read and serve compiled / gzipped file
    $head = $this->data['h'][$this->data['is']];

    if (file_exists($this->data['fn'][$this->data['is']]['gz'])) {
      header($head);
      header($this->data['h']['gz']);
      $flushedFile = $this->data['fn'][$this->data['is']]['gz'];
    } else if (file_exists($this->data['fn'][$this->data['is']]['min'])) {
      header($head);
      $flushedFile = $this->data['fn'][$this->data['is']]['min'];
    } else if (file_exists($this->data['fn'][$this->data['is']]['bulk'])) {
      header($head);
      $flushedFile = $this->data['fn'][$this->data['is']]['bulk'];
    }

    if ($flushedFile == false) {
      echo 'ERROR 05: FORCED FILES NOT FOUND!';
      die;
    }

    // Output file
    if (ob_get_contents()) { ob_end_clean(); }
    flush();
    readfile($flushedFile);
    exit;
    die;
  }

  function read_file ($f) {
    $handle = fopen($f, "rb");
    $data = fread($handle, filesize($f));
    fclose($handle);
    return $data;
  }

  function write_to_file ($f, $data) {
    if (file_exists($f)) { unlink($f); }
    $handle = fopen($f, "wb");
    $nA     = fwrite($handle, $data);
    fclose($handle);
    return $nA;
  }

  function write_to_file_gz ($f, $data) {
    if (file_exists($f)) { unlink($f); }
    $handle = gzopen ($f, 'w9');
    $nA = gzwrite ($handle, $data);
    gzclose($handle);
    return $nA;
  }

  function minify () {
    if ($this->data['is'] == 'css') {
      return $this->minify_css($this->data['b']);
      die;
    } else {
      return $this->uglify_js();
    }
  }

  function uglify_js () {
    $tf = '____t.js';
    if (file_exists($tf)) { unlink($tf);}
    // Uglify

    $command = '"' . $this->data['node']['exe'] . '" ';
    $command .= '"' . $this->data['node']['ugli'] . '" ';
    $command .= '-m -c -v ';
    $command .= $this->data['fn']['js']['bulk'] . ' > ' . $tf ; //. ' 2>&1'
    exec($command, $salida);

    // Minify buffer with JSMin.php
    // Not used
    /*if (file_exists("../_classes/jsmin.class.php")) {
      require "../_classes/jsmin.class.php";
      $buffer = JSMin::minify($buffer);
    }*/

    $data = $this->read_file($tf);
    if (file_exists($tf)) { unlink($tf);}

    return $data;
  }

  function minify_css ($data) {
    $data = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $data);
    $data = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $data);
    $data = preg_replace( '#\s+#', ' ', $data );
    $data = preg_replace( '#/\*.*?\*/#s', '', $data );
    $data = str_replace( '; ', ';', $data );
    $data = str_replace( ': ', ':', $data );
    $data = str_replace( ' {', '{', $data );
    $data = str_replace( '{ ', '{', $data );
    $data = str_replace( ', ', ',', $data );
    $data = str_replace( '} ', '}', $data );
    $data = str_replace( ';}', '}', $data );
    return $data;
  }
}

?>
