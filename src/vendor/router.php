<?php

namespace Helo\Vendor\Router;

use Helo\Library\Json\JsonReader,
    Helo\Library\Cache\Cache,
    Helo\Library\Network\Network,
    Helo\Vendor\Application\Application,
    Helo\Vendor\Requirer\Requirer;


/** 
 * Class used to find route and set application
 *
 * @package src
 * @subpackage vendor
 * @author Castellant Guillaume
 **/    
class Router
{

    private $cache;
    private $routing = array();
    private $api = array('GET', 'POST', 'PUT', 'DELETE');

    public function __construct()
    {
        $this->cache = new Cache();

        if (_ENV=='dev') {
            $deny = true;
            $json = new JsonReader();
            if (($env_ip = $json->read(_CONFIG.'env_ip.json'))!=false) {
                $network = new Network();
                $remote = $_SERVER['REMOTE_ADDR'];

                if (isset($env_ip->{_ENV})) {
                    foreach ($env_ip->{_ENV} as $ip) {
                        if (preg_match('/^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$/i', $ip)) {
                            if ($remote==$ip) {
                                $deny = false;
                            }
                        } else {
                            if ($network->in_range($remote, $ip)) {
                                $deny = false;
                            }
                        }
                    }
                }

                if ($deny) {
                    $this->deny();
                }
            }
        }
    }

    /**
     * Function called when no route found
     * 
     * @return void
     *
     */
    public function oups()
    {
        echo "OUPS: No route";
        exit();
    }

    /**
     * Function called when the access is deny
     * 
     * @return void
     *
     */
    public function deny()
    {
        echo "OUPS: Access deny";
        exit();
    }

    /**
     * Get application's route
     * 
     * @return boolean true if route was founded
     *
     */
    public function get()
    {
        $json = new JsonReader();
        $application = unserialize(_APPS_DEFINED);

        foreach ($application as $name => $app) {
            foreach ($app['sub'] as $sub => $a) {
                if (file_exists(($routing_file = $a['route']))) {
                    if (($routing = $json->read($routing_file))!=false) {
                        $this->rt[$name.':'.$sub] = $routing;
                    }
                }
            }
        }

        return (count($this->rt)>0);
    }

    /**
     * Detect and set the application object wich correspond to the route
     * 
     * @return Application object
     *
     */
    public function detect()
    {
        $hash = $this->cache->hash('ROUTE', _URI, serialize($this->rt));
        $route_find = array();
        if (_CONFIG_CACHE_ENABLE && ($cache_load = $this->cache->load($hash))!=false) {
            $route_find = $cache_load;
        } else {
            $request = $this->get_request_method();
            $confirm = false;
            $this->preformat_route();
            foreach ($this->rt as $route_app => $routing) {
                foreach ($routing as $route_pattern => $route_conf) {
                    $route_find = array();
                    $route_find['pattern'] = $route_pattern;

                    if (is_object($route_conf)) {
                        $route_find['controller'] = $route_app.':'.$route_conf->controller;

                        if (isset($route_conf->domain)) {
                            if ($route_conf->domain==_URI_DOMAIN) {
                                $route_find['domain'] = $route_conf->domain;
                            } else { 
                                continue;
                            }
                        }

                        if (isset($route_conf->subdomain)) {
                            if ($route_conf->subdomain==_URI_SUBDOMAIN) {
                                $route_find['subdomain'] = $route_conf->subdomain;
                            } else { 
                                continue;
                            }
                        }

                        if (isset($route_conf->method)) {
                            if ($route_conf->method=='API') {
                                if (in_array($request, $this->api)) {
                                    $route_find['method'] = $route_conf->method;
                                } else { 
                                    continue;
                                }
                            } else {
                                if($route_conf->method==$request) {
                                    $route_find['method'] = $route_conf->method;
                                } else { 
                                    continue;
                                }
                            }

                        }
                    } else {
                        $route_find['controller'] = $route_app.':'.$route_conf;
                    }

                    if ($route_pattern=='*' || $route_pattern==_URI) {
                        $confirm = true;
                        break 2;
                    } else {
                        $no_break  = false;
                        $vr_fd     = array();
                        $rt_fm     = array(); 
                        $route_split  = explode('/', $route_pattern);

                        foreach ($route_split as &$split) {
                            $split = str_replace(array('{{   ','{{  ','{{ '), '{{', $split);
                            $split = str_replace(array('   }}','  }}',' }}'), '}}', $split);

                            if (preg_match_all('/\{\{\s*(.*)\s*\}\}/Ui', $split, $rt_m)) {
                                if (count($rt_m[0])==1) {
                                    if (preg_match('/([a-zA-Z_]+)\:(.*)/i', $rt_m[1][0], $m)) {
                                        $rt_fm[] = str_replace('{{'.$m[0].'}}', $m[2], $split);
                                        $vr_fd[$m[1]] = null;
                                    } else {
                                        $rt_fm[] = str_replace($rt_m[0][0], "(.*)", $split);
                                        $vr_fd[$rt_m[1][0]] = null;
                                    }
                                } else {
                                    foreach ($rt_m[0] as $k => $value) {
                                        if (preg_match('/([a-zA-Z_]+)\:(.*)/i', $rt_m[1][$k], $m)) {
                                            $split = str_replace('{{'.$m[0].'}}', $m[2], $split);
                                            $vr_fd[$m[1]] = null;
                                        } else {
                                            $split = str_replace($value, "(.*)", $split);
                                            $vr_fd[$rt_m[1][$k]] = null;
                                        }
                                    }
                                    
                                    $rt_fm[] = $split;
                                }
                            } else {
                                $rt_fm[] = $split;
                            }
                        }

                        $route_reg_pattern = str_replace('/', '\\/', implode('/', $rt_fm));
                        if (preg_match("/^".$route_reg_pattern."$/i", _URI, $rt_m)) {
                            if (isset($rt_m[1]) && $rt_m[1]!=null) {
                                $i = 1;
                                foreach ($vr_fd as $var_name => &$var_val) {
                                    $var_val = $rt_m[$i];
                                    $i++;
                                }
                            }

                            $route_find['args'] = $vr_fd;
                            $confirm = true;
                            break 2;
                        }
                    }
                }
            }

            if (!$confirm) {
                $route_find = array();
            }

            if (_CONFIG_CACHE_ENABLE && count($route_find)>0) {
                $this->cache->save($hash, $route_find);
            }
        }

        if (count($route_find)>0) {
            return new Application(
                $route_find['controller'],
                (isset($route_find['args'])) ? $route_find['args'] : null
            );
        }

        return false;
    }

    /**
     * This method return the request method
     * 
     * @return string Indicate the request method
     *
     */
    private function get_request_method()
    {
        return (defined('_REQUEST')) ? _REQUEST : 'GET';
    }

    /**
     * This method format the multi route
     * 
     * @return void
     *
     */
    private function preformat_route()
    {

        $request = $this->get_request_method();

        foreach ($this->rt as $rt_app => $routings) {
            foreach ($routings as $rt_pat => $rt_cf) {
                if (preg_match('/^\_controller\-([a-zA-Z0-9]+)$/i', $rt_pat, $matches)) {
                    unset($this->rt[$rt_app]->$rt_pat);
                    
                    if (isset($rt_cf->path)) {
                        if (is_array($rt_cf->match)) {
                            foreach ($rt_cf->match as $m) {
                                if (isset($rt_cf->domain) || 
                                    isset($rt_cf->subdomain) || 
                                    isset($rt_cf->method)) {

                                    $this->rt[$rt_app]->{$m} = new \stdClass;
                                    $this->rt[$rt_app]->{$m}->controller = $rt_cf->path;

                                    if (isset($rt_cf->domain)) {
                                        $this->rt[$rt_app]->{$m}->domain = $rt_cf->domain;
                                    }

                                    if (isset($rt_cf->subdomain)) {
                                        $this->rt[$rt_app]->{$m}->subdomain = $rt_cf->subdomain;
                                    }

                                    if (isset($rt_cf->method)) {
                                        $this->rt[$rt_app]->{$m}->method = $rt_cf->method;
                                    }
                                } else {
                                    $this->rt[$rt_app]->{$m} = $rt_cf->path;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
