<?php
/**
 * Class Template, assign, set template and burn.
 *
 * @author Goncalo Margalho <gsky89@gmail.com>
 * @copyright devalien.Com (C) 2008-2010
 * @license LGPL(V3) http://www.opensource.org/licenses/lgpl-3.0.html
 * @version 1.0
 */

/**
 * Class Template, assign, set template and burn.
 *
 * @author Goncalo Margalho <gsky89@gmail.com>
 * @copyright devalien.Com (C) 2008-2010
 * @version 1.0
 */
class Template {

    /**
     * Content of all Variables
     *
     * @var array
     */
    private $variables;

    /**
     * Directory of Template (skins)
     *
     * @var string
     */
    private $tpl_base = './skins/';

    /**
     * The directory of the templates directory
     *
     * @var string
     */
    private $tpl_dir;

    /**
     * Masterpage of template
     *
     * @var string
     */
    private $masterpage;

    /**
     * String with the content of file to write in the cache file
     *
     * @var String
     */
    private $compiledFile;

    /**
     * Array with the contents parts for insert to the masterpage
     *
     * @var array
     */
    private $fileContent = array();

    /**
     * name of page
     *
     * @var string
     */
    private $name;

    /**
     * Set directory of Template and masterpage.
     *
     * @param string $template_dir Directory of Template
     *
     * @return Void
     */
    public function __construct($template_name, $default = true) {
        $this->name = $template_name;
        if($default == true)
            $this->tpl_dir = $this->tpl_base.$this->name;
        else
            $this->tpl_dir = $this->name;
        $this->masterpage = $this->tpl_dir.'/master.page';
    }

    /**
     * Compile the masterpage with the content of the tpl page and add the title, js and css etc.
     *
     * @return Void
     */
    private function compileMasterpage() {
        $this->compiledFile = file_get_contents($this->masterpage);
        $this->compileTPL($this->tpl_dir, 'master.page');
        $this->compiledFile = preg_replace_callback('/(?:{content(?:\s+)name="(.*?)"}([\S|\s]*?){\/content})/', array( &$this, 'loadContent'), $this->compiledFile);
    }
	
    /**
     * Assign variable to a template
     *
     * @param string $variable_name Name of variable in template
     * @param mixed $value Value of variable assigned
     *
     * @return Void
     */
    public function assign($variable_name, $value) {
        global $variables;
        $this->variables[ $variable_name ] = $value;
    }

    /**
     * Delete old compiled file and compile a new file
     *
     * @param string $template_dir Directory of template
     * @param string $tpl_name Name of template
     *
     * @return Void
     */
    private function compileTPL($template_dir, $tpl_name) {
        if(is_writable( $template_dir . '/Compiled'))
            if($file = glob( $template_dir . '/Compiled/' . $tpl_name . '*.php' ))
                foreach( $file as $delfile)
                    unlink($delfile);

        $tpl = file_get_contents($template_dir . '/' . $tpl_name);
        $compiling = preg_replace('/{\$(.[^}]*?)\.(.*?)}/', '<?php echo $\\1[\'\\2\'];?>',$tpl);
        $compiling = preg_replace('/{\_(.[^}]*?)\.(.*?)}/', '<?php echo $var[\'\\1\'][\'\\2\'];?>',$compiling);
        $compiling = preg_replace('/\[\$(.*?)\.(.*?)\]/', '$\\1[\'\\2\']',$compiling);
        $compiling = preg_replace('/{\$key\.(.*?)}/', '<?php echo $key[\'\\1\'];?>',$compiling);
        $compiling = preg_replace('/{date\.\$(.*?)\.(.*?)}/', '<?php echo date(\'d-m-Y H:i\',$\\1[\'\\2\']);?>',$compiling);
        $compiling = preg_replace('/{date\.time}/', '<?php echo date(\'d-m-Y H:i\',time());?>',$compiling);
        $compiling = preg_replace('/{date\.(.*?)}/', '<?php echo date(\'d-m-Y H:i\',$var[\'\\1\']);?>',$compiling);
        $compiling = preg_replace('/\[\$key\.(.*?)\]/', '$key[\'\\1\']',$compiling);
        $compiling = preg_replace('/\[\$value\]/', '$value',$compiling);
        $compiling = str_replace('{$value}', '<?php echo $value;?>',$compiling);
        $compiling = str_replace('{$key}', '<?php echo $key;?>',$compiling);
        $compiling = preg_replace('/{\$(.*?)}/', '<?php echo $var[\'\\1\'];?>',$compiling);
        $compiling = preg_replace('/\[\$(.*?)\]/', '$var[\'\\1\']',$compiling);
        $compiling = preg_replace('/{(.*?)\:\:(.*?)}/', '<?php echo \\1::\\2;?>',$compiling);
        $compiling = preg_replace('/\[counter.(.*?)\]/', '$counter_\\1',$compiling);
        $compiling = preg_replace('/(?:{if(?:\s+)condition="(.*?)"})/', '<?php if(\\1){ ?>',$compiling);
        $compiling = preg_replace('/(?:{elseif(?:\s+)condition="(.*?)"})/', '<?php } else if(\\1){ ?>',$compiling);
        $compiling = str_replace('{else}', '<?php } else{ ?>',$compiling);
        $compiling = str_replace('{/if}', '<?php } ?>',$compiling);
        $compiling = preg_replace('/(?:{loop(?:\s+)name="(.*?)"})/', '<?php $counter_\\1=0; foreach($var[\'\\1\'] as $key => $\\1){ $counter_\\1++; ?>',$compiling);
        $compiling = str_replace('{/loop}', '<?php } ?>',$compiling);
        if($tpl_name != 'master.page')
            $compiling = preg_replace_callback('/(?:{content(?:\s+)name="(.*?)"}([\S|\s]*?){\/content})/', array( &$this, 'setFileContent'), $compiling);

        $this->compiledFile = $compiling;
    }

    /**
     * Compile template, HTML to PHP
	 *
     * @global array $lang
     * @global User $user
     * @global array $qgeneral
     * @global Database $db
     * @global array $database
     * @param string $tpl_name Name of file to compile
     * @param string $ext Extension of file to compile
     * @param boolean $withMasterpage if you can use the masterpage. Default is true
     * @param boolean $echo if you can cache the file and after include it or print the compiled template
     */
    public function burn($tpl_name, $ext, $withMasterpage = true, $echo = false) {
        $var = $this->variables;
        if(!file_exists($this->tpl_dir . '/' . $tpl_name . '.' . $ext)) {
            echo 'The system tried to use the file: '. $this->tpl_dir . '/' . $tpl_name . '.' . $ext .' but doesn\'t exists<br /><br />Return to the <a href="'.$qgeneral['url_base'].'">site</a>';
            exit();
        }
        $tpltime = filemtime($this->tpl_dir . '/' . $tpl_name . '.' . $ext);
        if(file_exists($this->masterpage))
            $mastertime = filemtime($this->masterpage);
        else
            $mastertime = 0;
        if($echo == false) {
            if (file_exists($this->tpl_dir . '/Compiled/' . $tpl_name . '.' . $ext . '_' . $tpltime . '_' . $mastertime . '.php'))
                include $this->tpl_dir . '/Compiled/' . $tpl_name . '.' . $ext . '_' . $tpltime . '_' . $mastertime . '.php';
            else {
                $this->compileTPL($this->tpl_dir, $tpl_name . '.' . $ext, $withMasterpage);
                if($withMasterpage == true) {
                    $this->compileMasterpage();
                }
                if(is_writable($this->tpl_dir . '/Compiled/')) {
                    fwrite( fopen( $this->tpl_dir . '/Compiled/' . $tpl_name . '.' . $ext . '_' . $tpltime . '_' . $mastertime . '.php', 'w' ), $this->compiledFile );
                    include $this->tpl_dir . '/Compiled/' . $tpl_name . '.' . $ext . '_' . $tpltime . '_' . $mastertime . '.php';
                }
                else
                    eval('?>'.$this->compiledFile);
            }
        }
        else
            echo $this->compiledFile;
    }

    /**
     * Set the content of the "contents" in the fileContent array
     *
     * @param Array $content
     * @return String
     */
    private function setFileContent($content) {
        $this->fileContent[$content[1]] = $content[2];
        return $content[2];
    }

   
    /**
     * Load the contents
     *
     * @param array $matches
     * @return String
     */
    private function loadContent($matches) {
        if(array_key_exists($matches[1], $this->fileContent))
            return $this->fileContent[$matches[1]];

        return $matches[2];
    }
}
?>