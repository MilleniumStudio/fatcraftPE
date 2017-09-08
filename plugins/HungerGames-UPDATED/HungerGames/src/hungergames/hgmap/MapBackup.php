<?php
namespace hungergames\hgmap;
class MapBackup{
    /**
     * Writes folder backup
     *
     * @param $source
     * @param $destination
     * @return bool
     */
    public function write($source, $destination){
        $dir = opendir($source);
        @mkdir($destination);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($source . '/' . $file) ) {
                    $this->write($source . '/' . $file, $destination . '/' . $file);
                }
                else {
                    copy($source . '/' . $file, $destination . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    /**
     * Resets game map
     *
     * @param $source
     * @param $destination
     * @return bool
     */
    public function reset($source, $destination){
        $this->write($source, $destination);
    }
}