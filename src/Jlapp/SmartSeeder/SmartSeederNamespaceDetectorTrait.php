<?php
namespace Jlapp\SmartSeeder;

trait SmartSeederNamespaceDetectorTrait {
    public function getAppNamespace(){
        return "App\\";
    }
}