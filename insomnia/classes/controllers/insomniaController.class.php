<?php

class insomniaController extends baseController {
    public function run() {
        $insomnia = new insomnia();
        $insomnia->create();
    }
}

?>