<?php

namespace Tests\Traits;

trait TestStorages
{
    protected function deleteAllFiles()
    {
        //Acessa todos os diretorios e niveis, vamos usar o directies apenas para acessar o nível
        //Storage::allDirectories(directory);
        $dirs = \Storage::directories();
        foreach ($dirs as $dir) {
            $files = \Storage::files($dir);
            \Storage::delete($files);
            \Storage::deleteDirectory($dir);
        }
    }
}
