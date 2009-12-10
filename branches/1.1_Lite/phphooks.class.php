<?php
/**
 * @author eric.wzy@gmail.com
 * @version 1.1_lite
 * @package phphooks
 * @category Plugins
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */
define('PLUGINS_FOLDER', 'plugins/');
class Phphooks
{
    /**
     * plugins option data
     * @var array
     */
    var $plugins = array();
    /**
     * UNSET means load all plugins, which is stored in the plugin folder. ISSET just load the plugins in this array.
     * @var array
     */
    var $active_plugins = NULL;
    /**
     * all plugins header information array.
     * @var array
     */
    var $plugins_header = array();
    /**
     * hooks data
     * @var array
     */
    var $hooks = array();
    /**
     * load plugins from specific folder, includes *.plugin.php files
     * @package phphooks
     * @since 1.0
     * 
     * @param string $from_folder optional. load plugins from folder, if no argument is supplied, a 'plugins/' constant will be used
     */
    function load_plugins ($from_folder = PLUGINS_FOLDER)
    {
        if ($handle = @opendir($from_folder)) {
            while ($file = readdir($handle)) {
                if (is_file($from_folder . $file)) {
                    if (($this->active_plugins == NULL || in_array($file, $this->active_plugins)) && strpos($from_folder . $file, '.plugin.php')) {
                        require_once $from_folder . $file;
                        $this->plugins[$file]['file'] = $file;
                    }
                } else 
                    if ((is_dir($from_folder . $file)) && (substr($file,0,1) != '.')) {
                        $this->load_plugins($from_folder . $file . '/');
                    }
            }
            closedir($handle);
        }
    }
    /**
     * return the all plugins ,which is stored in the plugin folder, header information.
     * 
     * @package phphooks
     * @since 1.1
     * @param string $from_folder optional. load plugins from folder, if no argument is supplied, a 'plugins/' constant will be used
     * @return array. return the all plugins ,which is stored in the plugin folder, header information.
     */
    function get_plugins_header ($from_folder = PLUGINS_FOLDER)
    {
        if ($handle = @opendir($from_folder)) {
            while ($file = readdir($handle)) {
                if (is_file($from_folder . $file)) {
                    if (strpos($from_folder . $file, '.plugin.php')) {
                        $fp = fopen($from_folder . $file, 'r');
                        // Pull only the first 8kiB of the file in.
                        $plugin_data = fread($fp, 8192);
                        fclose($fp);
                        preg_match('|Plugin Name:(.*)$|mi', $plugin_data, $name);
                        preg_match('|Plugin URI:(.*)$|mi', $plugin_data, $uri);
                        preg_match('|Version:(.*)|i', $plugin_data, $version);
                        preg_match('|Description:(.*)$|mi', $plugin_data, $description);
                        preg_match('|Author:(.*)$|mi', $plugin_data, $author_name);
                        preg_match('|Author URI:(.*)$|mi', $plugin_data, $author_uri);
                        foreach (array('name' , 'uri' , 'version' , 'description' , 'author_name' , 'author_uri') as $field) {
                            if (! empty(${$field}))
                                ${$field} = trim(${$field}[1]);
                            else
                                ${$field} = '';
                        }
                        $plugin_data = array('filename' => $file , 'Name' => $name , 'Title' => $name , 'PluginURI' => $uri , 'Description' => $description , 'Author' => $author_name , 'AuthorURI' => $author_uri , 'Version' => $version);
                        $this->plugins_header[] = $plugin_data;
                    }
                } else 
                    if ((is_dir($from_folder . $file)) && (substr($file,0,1) != '.')) {
                        $this->get_plugins_header($from_folder . $file . '/');
                    }
            }
            closedir($handle);
        }
        return $this->plugins_header;
    }
    /**
     * attach custom function to hook
     * @package phphooks
     * @since 1.0
     * 
     * @param string $tag. The name of the hook.
     * @param string $function. The function you wish to be called.
     * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
     */
    function add_hook ($tag, $function, $priority = 10)
    {
        $tag = trim ($tag);
        $this->hooks[$tag][$priority][] = $function;
    }
    /**
     * check whether any function is attached to hook
     * @package phphooks
     * @since 1.0
     * 
     * @param string $tag The name of the hook.
     */
    function hook_exist ($tag)
    {
        $tag = trim ($tag);
        return isset($this->hooks[$tag]) ? true : false;
    }
    /**
     * execute all functions which are attached to hook, you can provide argument (or arguments via array)
     * @package phphooks
     * @since 1.0
     * 
     * @param string $tag. The name of the hook.
     * @param mix $args optional.The arguments the function accept (default none)
     */
    function execute_hook ($tag, $args = '')
    {
        $tag = trim ($tag);
        $these_hooks = $this->hooks[$tag];
        uksort($these_hooks, array($this , "my_sort"));
        foreach ($these_hooks as $hooks) {
            foreach ($hooks as $hook) {
                call_user_func($hook, $args);
            }
        }
    }
    /**
     * filter $args and after modify, return it. (or arguments via array)
     * @package phphooks
     * @since 1.0
     * 
     * @param string $tag. The name of the hook.
     * @param mix $args optional.The arguments the function accept to filter(default none)
     * @return array. The $args filter result.
     */
    function filter_hook ($tag, $args)
    {
        $tag = trim ($tag);
        $these_hooks = $this->hooks[$tag];
        uksort($these_hooks, array($this , "my_sort"));
        foreach ($these_hooks as $hooks) {
            foreach ($hooks as $hook) {
                $args = call_user_func($hook, $args);
            }
        }
        return $args;
    }
    /**
     * register plugin data in $this->plugin
     * @package phphooks
     * @since 1.0
     * 
     * @param string $plugin_id. The name of the plugin.
     * @param array $data optional.The data the plugin accessorial(default none)
     */
    function register_plugin ($plugin_id, $data = '')
    {
        foreach ($data as $key => $value) {
            $this->plugins[$plugin_id][$key] = $value;
        }
    }
    /**
     * sort hooks priority
     */
    private function my_sort ($a, $b)
    {
        if ($a == $b)
            return 0;
        return ($a < $b) ? - 1 : 1;
    }
}
?>