<?php

namespace Tests\Traits;

trait TestStorages
{
    protected function deleteAllFiles()
    {
        //Acessa os diretórios e objetos de forma recursiva
        //Storage::allDirectories(directory);
        //Acessa os diretórios no mesmo nível de diretório
        $dirs = \Storage::directories();
        foreach ($dirs as $dir) {
            $files = \Storage::files($dir);
            \Storage::delete($files);
            \Storage::deleteDirectory($dir);
        }
    }
}
