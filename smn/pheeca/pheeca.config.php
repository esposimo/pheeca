<?php
include __DIR__ .'/compatibility.php'; // compatibiltà
require_once __DIR__  .'/kernel/Loader.php'; // richiamo manualmente la classe dell'autoloader. __DIR__ è corretto come prefisso, tanto il file deve stare qua nella root di pheeca e non muoversi
\smn\pheeca\kernel\Loader::addClassPath(PHEECA_ROOT); // aggiungo il framework all'autoloader. la PHEECA_ROOT va dichiarata nell'index da chi usa il framework.
spl_autoload_register(array('\smn\pheeca\kernel\Loader', 'loadClass')); // registro la funzione che sarà usata per caricare i file



define('RENDER_DEBUG', true);
if (RENDER_DEBUG) {
    error_reporting(E_ALL); /**/
}



/* functions */

function normalize_pathfile($file) {
    $temp = preg_replace('/\/\//', '/', preg_replace('/\x5c/', '/', $file));
    while (str_replace('//', '/', $temp) != $temp) {
        $temp = str_replace('//', '/', $temp);
    }
    return $temp;
}

function _t($string, $language = null, $domain = null) {
    return \smn\pheeca\kernel\Translate::_($string, $language, $domain);
}


// directory radice del framework
//define('PHEECA_ROOT', normalize_pathfile(USHB_ROOT .'/pheeca')); // path del pheeca framework
//define('RENDER_CORE_PATH', normalize_pathfile(RENDER_ROOT . '/smn')); // questa ERA la root del framework, sostituita da USHB_ROOT che è diventata la root di tutti i progetti
//define('RENDER_PHEECA_PATH', RENDER_CORE_PATH . '/pheeca'); // root del framework
//define('RENDER_KERNEL_PATH', RENDER_PHEECA_PATH . '/kernel'); // root del kernel (sotto dir del framework)
//define('RENDER_TEMPLATE_PATH', RENDER_CORE_PATH . '/pheeca/templates');

//define('RENDER_CORE_LOGNAME', 'core'); // core log del framework


//spl_autoload_register(function ($class) {
//    $explodeRequest = explode('\\', $class);
//    $requestClass = end($explodeRequest);
//    //$namespace_root = reset($explodeRequest);
//    $filename = USHB_ROOT . '/' . preg_replace('/\x5c/', '/', $class) . RENDER_EXTENSION_CLASS;
//    
//    if (is_readable($filename)) {
//        require_once $filename;
//    } else {
////        if ($explodeRequest[2] == 'override') {
////            $e = $explodeRequest;
////            end($e);
////            unset($e[key($e)]);
////            $ns_without_class = implode('\\', $e);
////
////            $c = $explodeRequest;
////            unset($c[2]);
////            $no_override_ns = implode('\\', $c);
////
////            $parent_class = RENDER_ROOT . '/' . preg_replace('/\x5c/', '/', $no_override_ns) . RENDER_EXTENSION_CLASS;
////
////            if (is_readable($parent_class)) {
////                $string = 'namespace ' . $ns_without_class . '; '; // inizio ad inserire il namespace
////                if (\trait_exists($no_override_ns)) {
////                    // i traits non si overridano come le classi o le interfacce.
////                    // il traits parents devono essere inseriti nel traits che esegue l'override con 'use'
////                    $string .= 'trait ' . $requestClass . ' { use \\' . $no_override_ns . '; }';
////                } else if (\interface_exists($no_override_ns)) {
////                    // le interfacce possono essere estese al pari di una classe
////                    $string .= 'interface ' . $requestClass . ' extends \\' . $no_override_ns . '{ }';
////                } else {
////                    $string .= 'class ' . $requestClass . ' extends \\' . $no_override_ns . '{ }';
////                }
////                eval($string);
////            }
////        }
//    }
//}, false, true);

//spl_autoload_register(function ($class) {
//    $explodeRequest = explode('\\', $class);
//    $requestClass = end($explodeRequest);
//    //$namespace_root = reset($explodeRequest);
//    $filename = USHB_ROOT . '/' . preg_replace('/\x5c/', '/', $class) . RENDER_EXTENSION_CLASS;
//    
//    if (is_readable($filename)) {
//        require_once $filename;
//    }
//}, false, true);













// Loggin initialization
// inizializza i writer dei log
// Database initialization


