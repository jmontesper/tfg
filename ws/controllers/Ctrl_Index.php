<?php

/**
 * Controlador Index, este es el controlador que atiende una petición que simplemente
 * quiere ver el dominio https://pk.bitcampus.eu
 */
class Ctrl_Index extends \zfx\Controller
{
    public function _main()
    {
        // Mostramos una página de cortesía indicando que es un servicio web de un TFG de la UOC.
        \zfx\View::direct('indice');
    }
}
