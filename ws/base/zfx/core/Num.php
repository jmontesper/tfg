<?php
/*
  Zerfrex (R) Web Framework (ZWF)

  Copyright (c) 2012-2022 Jorge A. Montes Pérez <jorge@zerfrex.com>
  All rights reserved.

  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions
  are met:
  1. Redistributions of source code must retain the above copyright
  notice, this list of conditions and the following disclaimer.
  2. Redistributions in binary form must reproduce the above copyright
  notice, this list of conditions and the following disclaimer in the
  documentation and/or other materials provided with the distribution.
  3. Neither the name of copyright holders nor the names of its
  contributors may be used to endorse or promote products derived
  from this software without specific prior written permission.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
  ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
  TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
  PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL COPYRIGHT HOLDERS OR CONTRIBUTORS
  BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
  SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
  INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
  CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
  ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
  POSSIBILITY OF SUCH DAMAGE.
 */

namespace zfx;

/**
 * Class Num
 *
 * Representa un nùmero de coma fija y permite realizar las operaciones más habituales
 */
class Num
{
    // Esta es la precisión, siempre fija
    const PRE = 6;

    // Este es el valor.
    protected $val;

    // --------------------------------------------------------------------

    /**
     * Obtener el valor como representación interna.
     *
     * @return string Número en representación interna
     */
    public function getVal()
    {
        return $this->val;
    }

    // --------------------------------------------------------------------

    /**
     * Establecer el valor
     *
     * @param string $val Representación del número como cadena o en otro caso se intentará hacer casting
     * @param bool $check Si es TRUE (por defecto), se limpia y desparasita. Útil cuando no controlamos lo que se ha podido introducir
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function setVal($val, $check = TRUE)
    {
        // Lo vamos a tratar como una cadena
        $val = (string)$val;

        if ($check) {

            // Si aparece un signo menos en cualquier sitio, entonces el número será negativo
            $signo = '';
            if (preg_match('%[-]%u', $val)) {
                $signo = '-';
            }

            // Eliminamos cosas:
            $val = preg_replace(
                [
                    '%[,.](?=.*?[,.])%',   // Cualquier coma o punto que no sea el último será eliminado
                    '%[^.,0-9]%u',         // Cualquier carácter que no sea un dígito, la coma o el punto será eliminado
                    '%[.,]%',              // Finalmente cualquier coma es convertida a punto
                ],
                [
                    '',
                    '',
                    '.'
                ]
                , $val);

            // Si nos sale una cadena en blanco, el número es cero
            if ($val === '') {
                $val = '0';
            }

            // Si hay un punto al final, lo suprimimos.
            if (substr($val, -1) == '.') {
                $val = substr($val, 0, -1);
            }
            // Si hay un punto al principio, la parte entera es cero
            if (substr($val, 0, 1) == '.') {
                $val = '0' . $val;
            }

            // Quitamos muchos ceros
            $val = preg_replace(
                [
                    '%^0+$%siu',        // 0000000
                    '%^0+\.0+$%siu',    // 0000.0000
                    '%^0+\.%siu',       // 00000.
                    '%\.0+$%siu',       // .000000
                    '%^0+(?!\.)%siu'    // 000000n
                ],
                [
                    '0',
                    '0',
                    '0.',
                    '',
                    ''
                ], $val);

            // Si nos sale una cadena en blanco de nuevo, el número es cero.
            if ($val === '') {
                $val = '0';
            }
            // Finalmente, si no es cero, se le puede poner el signo.
            if ($val !== '0') {
                $val = $signo . $val;
            }
        }
        $this->val = $val;
    }

    // --------------------------------------------------------------------

    /**
     * Num constructor. Llamada idéntica a setVal()
     *
     * @param string $val
     * @param bool $check
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function __construct($val = '0', $check = TRUE)
    {
        $this->setVal($val, $check);
    }

    // --------------------------------------------------------------------

    /**
     * El método __toString() se proporciona por conveniencia, seguramente será más útil
     * sobreescribirlo y usar format()
     *
     * @return string
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function __toString()
    {
        return $this->getVal();
    }


    // --------------------------------------------------------------------

    /**
     * Formatear el número. Los valores predeterminados corresponden al Español de España
     *
     * @param int $decs Número de decimales a mostrar
     * @param string $dsep Separador de decimales
     * @param string $tsep Separador de miles
     * @param bool $crop Si es TRUE el número se corta a los decimales que se piden. Si es FALSE el número se redondea a los decimales que se piden. Por defecto es FALSE
     * @return string
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function format($decs = 2, $dsep = ',', $tsep = '.', $crop = FALSE)
    {
        // Si nos pasamos pidiendo decimales, el límite superior será nuestra precisión
        if ($decs > self::PRE) {
            $decs = self::PRE;
        }

        // Si no hay que cortar, redondeamos los decimales en una nueva instancia
        if (!$crop) {
            $num = $this->roundMe($decs);
        } // Si hay que cortar, podemos usar la propia instancia.
        else {
            $num = $this;
        }

        // Determinamos la parte decimal a devolver
        if ($decs > 0) {
            $dPart = $dsep . substr(self::ir_dec($this->getVal()) . str_repeat('0', self::PRE), 0, $decs);
        }
        else {
            $dPart = '';
        }

        // Determinamos la parte entera
        $sign    = '';
        $intPart = self::ir_int($this->getVal());
        if (substr($intPart, 0, 1) == '-') {
            $intPart = strrev(substr($intPart, 1));
            $sign    = '-';
        }
        else {
            $intPart = strrev($intPart);
        }
        $iPart = '';
        $start = strlen($intPart) - 1;
        for ($i = $start; $i >= 0; $i--) {
            $iPart .= substr($intPart, $i, 1);
            if ($i % 3 == 0 && $i != 0) {
                $iPart .= $tsep;
            }
        }

        return $sign . $iPart . $dPart;
    }

    // --------------------------------------------------------------------

    /**
     * Sumar un número al actual número
     * El resultado se devuelve como otra instancia diferente
     *
     * @param mixed $x Número a sumar. Puede ser un Num o algo que entiende el constructor de Num
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function add($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return new Num(bcadd($this->getVal(), $x->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Sumar un número al actual número
     * El resultado pasa a sustitur el valor de la actual instancia
     *
     * @param mixed $x Número a sumar. Puede ser un Num o algo que entiende el constructor de Num
     * @return $this
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function addMe($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        $this->setVal(bcadd($this->getVal(), $x->getVal(), self::PRE), FALSE);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Multiplicar un número por el actual número
     * El resultado se devuelve como otra instancia diferente
     *
     * @param $x mixed Número a multiplicar. Puede ser un Num o algo que entiende el constructor de Num
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function mul($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return new Num(bcmul($this->getVal(), $x->getVal(), self::PRE), FALSE);
    }


    // --------------------------------------------------------------------


    /**
     * Multiplicar un número por el actual número
     * El resultado pasa a sustitur el valor de la actual instancia
     *
     * @param mixed $x Número a multiplicar. Puede ser un Num o algo que entiende el constructor de Num
     * @return $this
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function mulMe($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        $this->setVal(bcmul($this->getVal(), $x->getVal(), self::PRE), FALSE);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Dividir un número, que será el divisor, por el actual número, que será el dividendo
     * El resultado (cociente) se devuelve como otra instancia diferente
     *
     * @param $x mixed Número por el que dividir. Puede ser un Num o algo que entiende el constructor de Num
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function div($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return new Num(bcdiv($this->getVal(), $x->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Dividir un número, que será el divisor, por el actual número, que será el dividendo
     * El resultado (cociente) pasa a sustitur el valor de la actual instancia
     *
     * @param mixed $x Número por el que dividir. Puede ser un Num o algo que entiende el constructor de Num
     * @return $this
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function divMe($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        $this->setVal(bcdiv($this->getVal(), $x->getVal(), self::PRE), FALSE);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Dividir el actual número, que será el dividendo, entre cierto número especificado, que será el divisor.
     * El resultado (cociente) pasa a sustitur el valor de la actual instancia
     * Si el divisor es cero, el resultado es cero.
     *
     * @param mixed $x Número por el que dividir. Puede ser un Num o algo que entiende el constructor de Num
     * @return $this
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function divzMe($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if ($x->eq(0)) {
            $this->setVal(0);
        }
        else {
            $this->setVal(bcdiv($this->getVal(), $x->getVal(), self::PRE), FALSE);
        }
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Restar un número (sustraendo) al actual número (minuendo)
     * El resultado se devuelve como otra instancia diferente
     *
     * @param mixed $x Número a sustraer. Puede ser un Num o algo que entiende el constructor de Num
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function sub($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return new Num(bcsub($this->getVal(), $x->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Restar un número (sustraendo) al actual número (minuendo)
     * El resultado pasa a sustitur el valor de la actual instancia
     *
     * @param mixed $x Número a sustraer. Puede ser un Num o algo que entiende el constructor de Num
     * @return $this
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function subMe($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        $this->setVal(bcsub($this->getVal(), $x->getVal(), self::PRE), FALSE);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Elevar el actual número (base) al número especificado (exponente)
     * El resultado se devuelve como otra instancia diferente
     *
     * @param $x mixed Exponente. Puede ser un Num o algo que entiende el constructor de Num
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function pow($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return new Num(bcpow($this->getVal(), $x->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Elevar el actual número (base) al número especificado (exponente)
     * El resultado pasa a sustitur el valor de la actual instancia
     *
     * @param $x mixed Exponente. Puede ser un Num o algo que entiende el constructor de Num
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function powMe($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        $this->setVal(bcpow($this->getVal(), $x->getVal(), self::PRE), FALSE);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Dividir un número, que será el divisor, por el actual número, que será el dividendo
     * El resto se devuelve como otra instancia diferente
     *
     * @param $x mixed Número por el que dividir. Puede ser un Num o algo que entiende el constructor de Num
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function mod($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return new Num(bcmod($this->getVal(), $x->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Dividir un número, que será el divisor, por el actual número, que será el dividendo
     * El resto pasa a sustitur el valor de la actual instancia
     *
     * @param mixed $x Número por el que dividir. Puede ser un Num o algo que entiende el constructor de Num
     * @return $this
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function modMe($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        $this->setVal(bcmod($this->getVal(), $x->getVal(), self::PRE), FALSE);
        return $this;
    }


    // --------------------------------------------------------------------

    /**
     * Obtener la raíz cuadrada del actual número (radicando)
     * La raiz se devuelve como otra instancia diferente
     *
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function sqrt()
    {
        return new Num(bcsqrt($this->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Obtener la raíz cuadrada del actual número (radicando)
     * La raiz pasa a sustitur el valor de la actual instancia
     *
     * @return $this
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function sqrtMe()
    {
        $this->setVal(bcsqrt($this->getVal(), self::PRE), FALSE);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Redondear por el método comercial el actual número dejándolo con los decimales especificados
     * El resultado se devuelve como otra instancia diferente
     *
     * @param $x
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function round($x)
    {
        if ($x < 0) {
            $x = 0;
        }
        $sign = '';
        if (bccomp('0', $this->getVal(), self::PRE) == 1) {
            $sign = '-';
        }
        $increment = $sign . '0.' . str_repeat('0', $x) . '5';
        $z         = bcadd($this->getVal(), $increment, $x + 1);
        return new Num(bcadd($z, '0', $x));
    }

    // --------------------------------------------------------------------

    /**
     * Redondear por el método comercial el actual número dejándolo con los decimales especificados
     * El resultado pasa a sustitur el valor de la actual instancia
     *
     * @param $x
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function roundMe($x)
    {
        if ($x < 0) {
            $x = 0;
        }
        $sign = '';
        if (bccomp('0', $this->getVal(), self::PRE) == 1) {
            $sign = '-';
        }
        $increment = $sign . '0.' . str_repeat('0', $x) . '5';
        $z         = bcadd($this->getVal(), $increment, $x + 1);
        $this->setVal(bcadd($z, '0', $x));
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Redondear hacia abajo el actual numero: ⌊this⌋
     * En realidad se redondea hacia la izquierda de la recta real.
     * - Si es positivo se devuelve la parte entera: ⌊5,34⌋ = 5
     * - Si es negativo se devuelve el entero anterior: ⌊-5,34⌋ = -6
     * El resultado se devuelve como otra instancia diferente
     *
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function floor()
    {
        // Veamos si es negativo
        if ($this->lt0()) {
            // Sí, Es negativo
            // Veamos si tiene parte decimal.
            if (bccomp('0', self::ir_dec($this->getVal()), self::PRE) == -1) {
                // Si tiene parte decimal es el anterior
                return new Num('-' . bcadd('-1', self::ir_int($this->getVal())));
            }
            else {
                // Si no tenía parte decimal pues es el mismo numero
                return new Num(self::ir_int($this->getVal()));
            }
        }
        else {
            // Es positivo
            return new Num(self::ir_int($this->getVal()));
        }
    }

    // --------------------------------------------------------------------

    /**
     * Redondear hacia abajo el actual numero: ⌊this⌋
     * En realidad se redondea hacia la izquierda de la recta real.
     * - Si es positivo se devuelve la parte entera: ⌊5,34⌋ = 5
     * - Si es negativo se devuelve el entero anterior: ⌊-5,34⌋ = -6
     * El resultado pasa a sustitur el valor de la actual instancia
     *
     * @return Num
     */
    public function floorMe()
    {
        // Veamos si es negativo
        if ($this->lt0()) {
            // Sí, Es negativo
            // Veamos si tiene parte decimal.
            if (bccomp('0', self::ir_dec($this->getVal()), self::PRE) == -1) {
                // Si tiene parte decimal es el anterior
                $this->setVal('-' . bcadd('-1', self::ir_int($this->getVal())));
            }
            else {
                // Si no tenía parte decimal pues es el mismo numero
                $this->setVal(self::ir_int($this->getVal()));
            }
        }
        else {
            // Es positivo
            $this->setVal(self::ir_int($this->getVal()));
        }
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Redondear hacia arriba el actual numero: ⌈this⌉
     * En realidad se redondea hacia la derecha de la recta real.
     * - Si es positivo se devuelve el entero siguiente: ⌈5,34⌉ = 6
     * - Si es negativo se devuelve su parte entera ⌈-5,34⌉ = -5
     * El resultado se devuelve como otra instancia diferente
     *
     * @return Num
     */
    public function ceil()
    {
        // Veamos si es negativo
        if ($this->lt0()) {
            // Sí, Es negativo
            return new Num(self::ir_int($this->getVal()));
        }
        else {
            // Es positivo o cero.
            // Veamos si tiene parte decimal.
            if (bccomp('0', self::ir_dec($this->getVal()), self::PRE) == -1) {
                // Sí tiene parte decimal, devolver el siguiente entero
                return new Num(bcadd('1', self::ir_int($this->getVal())));
            }
            else {
                // No tiene parte decimal, devolver el mismo numero.
                return new Num($this->getVal());
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Redondear hacia arriba el actual numero: ⌈this⌉
     * En realidad se redondea hacia la derecha de la recta real.
     * - Si es positivo se devuelve el entero siguiente: ⌈5,34⌉ = 6
     * - Si es negativo se devuelve su parte entera ⌈-5,34⌉ = -5
     * El resultado pasa a sustitur el valor de la actual instancia
     *
     * @return Num la propia instancia
     */
    public function ceilMe()
    {
        // Veamos si es negativo
        if ($this->lt0()) {
            // Sí, Es negativo
            $this->setVal(self::ir_int($this->getVal()));
        }
        else {
            // Es positivo o cero.
            // Veamos si tiene parte decimal.
            if (bccomp('0', self::ir_dec($this->getVal()), self::PRE) == -1) {
                // Sí tiene parte decimal, devolver el siguiente entero
                $this->setVal(bcadd('1', self::ir_int($this->getVal())));
            }
            else {
                // No tiene parte decimal, devolver el mismo numero.
                $this->setVal($this->getVal());
            }
        }
        return $this;
    }


    // --------------------------------------------------------------------

    /**
     * Determinar si el actual número es mayor que el especificado
     *
     * @param $x
     * @return bool True si la instancia es mayor, false en el resto de casos
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function gt($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return (bccomp($this->getVal(), $x->getVal(), self::PRE) === 1);
    }

    // --------------------------------------------------------------------


    /**
     * Determinar si el actual número es menor que el especificado
     *
     * @param $x
     * @return bool True si la instancia es menor, false en el resto de casos
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function lt($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return (bccomp($this->getVal(), $x->getVal(), self::PRE) === -1);
    }

    // --------------------------------------------------------------------

    /**
     * Determinar si el actual número es igual que el especificado
     *
     * @param $x
     * @return bool True si la instancia es igual, false en el resto de casos
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function eq($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return (bccomp($this->getVal(), $x->getVal(), self::PRE) === 0);
    }

    // --------------------------------------------------------------------

    /**
     * Determinar si el actual número es negativo
     *
     * @return bool True si es negativo, false si es positivo o cero.
     */
    public function lt0()
    {
        if (bccomp('0', $this->getVal(), self::PRE) == 1) {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }

    // --------------------------------------------------------------------
    // ESTÁTICOS
    // --------------------------------------------------------------------

    /**
     * Sumar los números especificados y devolver un nuevo número con el resultado
     *
     * @param $x
     * @param $y
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opAdd($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        return new Num(bcadd($x->getVal(), $y->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Multiplicar los números especificados y devolver un nuevo número con el resultado
     *
     * @param $x
     * @param $y
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opMul($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        return new Num(bcmul($x->getVal(), $y->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Restar los números especificados y devolver un nuevo número con el resultado
     *
     * @param $x mixed Minuendo
     * @param $y mixed Sustrayendo
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opSub($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        return new Num(bcsub($x->getVal(), $y->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Devolver el menor número de los especificados como Número
     * @param $x
     * @param $y
     * @return Num
     */
    static function opMin($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        if ($x->lt($y)) {
            return $x;
        }
        else {
            return $y;
        }
    }

    /**
     * Devolver el mayor número de los especificados como Número
     * @param $x
     * @param $y
     * @return Num
     */
    static function opMax($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        if ($x->lt($y)) {
            return $y;
        }
        else {
            return $x;
        }
    }

    /**
     * Devolver el par de Números ordenados de menor a mayor
     * @param $x
     * @param $y
     * @return Num[]
     */
    static function opOrd($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        if ($x->lt($y)) {
            return [$x, $y];
        }
        else {
            return [$y, $x];
        }
    }

    // --------------------------------------------------------------------

    /**
     * Dividir los números especificados y devolver un nuevo número con el cociente
     *
     * @param $x mixed Dividendo
     * @param $y mixed Divisor
     * @return Num Cociente
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opDiv($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        return new Num(bcdiv($x->getVal(), $y->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Dividir los números especificados y devolver un nuevo número con el resto
     *
     * @param $x Dividendo
     * @param $y Divisor
     * @return Num Resto
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opMod($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        return new Num(bcmod($x->getVal(), $y->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Ejecutar una operación exponencial con los números especificados y devolver un nuevo número con el resultado
     *
     * @param $x Base
     * @param $y Exponente
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opPow($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        return new Num(bcpow($x->getVal(), $y->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Obtener la raíz cuadrada del número especificado y devolver un nuevo número con el resultado
     *
     * @param $x
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opSqrt($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return new Num(bcsqrt($x->getVal(), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Comparar los números especificados y determinar si el primero es mayor que el segundo
     *
     * @param $x Primero
     * @param $y Segundo
     * @return bool
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opGt($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        return (bccomp($x->getVal(), $y->getVal(), self::PRE) === 1);
    }

    // --------------------------------------------------------------------

    /**
     * Comparar los números especificados y determinar si el primero es menor que el segundo
     *
     * @param $x Primero
     * @param $y Segundo
     * @return bool
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opLt($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        return (bccomp($x->getVal(), $y->getVal(), self::PRE) === -1);
    }

    // --------------------------------------------------------------------

    /**
     * Comparar los números especificados y determinar si son iguales
     *
     * @param $x
     * @param $y
     * @return bool
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opEq($x, $y)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        if (!($y instanceof self)) {
            $y = new Num($y);
        }
        return (bccomp($x->getVal(), $y->getVal(), self::PRE) === 0);
    }

    // --------------------------------------------------------------------

    /**
     * Redondear un número a los decimales especificados, devolviendo un nuevo número con el resultado
     *
     * @param $x Número a redondear
     * @param $y Cantidad de decimales
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */

    static function opRound($x, $y = 0)
    {
        if (!($x instanceof Num)) {
            $x = new Num($x);
        }
        if ($y < 0) {
            $y = 0;
        }
        $sign = '';
        if (bccomp('0', $x, self::PRE) == 1) {
            $sign = '-';
        }
        $increment = $sign . '0.' . str_repeat('0', $y) . '5';
        $x         = bcadd($x, $increment, $y + 1);
        return new Num(bcadd($x, '0', $y));
    }

    // --------------------------------------------------------------------


    /**
     * Redondear hacia abajo el actual numero: ⌊this⌋
     * En realidad se redondea hacia la izquierda de la recta real.
     * - Si es positivo se devuelve la parte entera: ⌊5,34⌋ = 5
     * - Si es negativo se devuelve el entero anterior: ⌊-5,34⌋ = -6
     * El resultado se devuelve como una instancia de Num
     *
     * @param $x Numero a redondear
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opFloor($x)
    {
        if (!($x instanceof Num)) {
            $x = new Num($x);
        }
        $x->floorMe();
        return $x;
    }

    // --------------------------------------------------------------------

    /**
     * Redondear hacia arriba el actual numero: ⌈this⌉
     * En realidad se redondea hacia la derecha de la recta real.
     * - Si es positivo se devuelve el entero siguiente: ⌈5,34⌉ = 6
     * - Si es negativo se devuelve su parte entera ⌈-5,34⌉ = -5
     * El resultado se devuelve como una instancia de Num
     *
     * @param $x Numero a redondear
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    static function opCeil($x)
    {
        if (!($x instanceof Num)) {
            $x = new Num($x);
        }
        $x->ceilMe();
        return $x;
    }

    // --------------------------------------------------------------------
    // PORCENTAJES
    // --------------------------------------------------------------------

    /**
     * Calcular el porcentaje del número actual.
     *
     * Si el número actual es N, entonces perc(X) devuelve N*X/100.
     * Ejemplo: si el número actual es 200, perc(5) devuelve el 5% de 200, o sea, 10.
     *
     * @param $x
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function perc($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return new Num(bcdiv(bcmul($this->getVal(), $x->getVal(), self::PRE), '100', self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Añadir al número actual el porcentaje indicado y devolver un nuevo número con el resultado
     *
     * Si el número actual es N, entonces percAdd(X) devuelve N + N*X/100.
     * Ejemplo: Si el número actual es 200, percAdd(5) calcula el 5% de 200 que es 10 y se lo suma, devolviendo 210.
     *
     * @param $x Porcentaje. Puede ser un Num o algo que entiende el constructor de Num
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function percAdd($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return new Num(bcadd($this->getVal(), bcdiv(bcmul($this->getVal(), $x->getVal(), self::PRE), '100', self::PRE), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Añadir al número actual el porcentaje indicado
     * El resultado pasa a sustitur el valor de la actual instancia
     *
     * Si el número actual es N, entonces percAddMe(X) devuelve N + N*X/100.
     * Ejemplo: Si el número actual es 200, percAdd(5) calcula el 5% de 200 que es 10 y se lo suma, devolviendo 210,
     * que pasa a sustituir al número actual.
     * Esta función es útil para impuestos como el IVA.
     *
     * @param mixed $x Porcentaje. Puede ser un Num o algo que entiende el constructor de Num
     * @return $this
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function percAddMe($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        $this->setVal(bcadd($this->getVal(), bcdiv(bcmul($this->getVal(), $x->getVal(), self::PRE), '100', self::PRE), self::PRE), FALSE);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Restar al número actual el porcentaje indicado y devolver un nuevo número con el resultado
     *
     * Si el número actual es N, entonces percSub(X) devuelve N - N*X/100.
     * Ejemplo: Si el número actual es 200, percSub(5) calcula el 5% de 200 que es 10 y se lo resta, devolviendo 190.
     * Esta función es útil para descuentos como rebajas de precio.
     *
     * @param $x Porcentaje. Puede ser un Num o algo que entiende el constructor de Num
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function percSub($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return new Num(bcsub($this->getVal(), bcdiv(bcmul($this->getVal(), $x->getVal(), self::PRE), '100', self::PRE), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Restar al número actual el porcentaje indicado
     * El resultado pasa a sustitur el valor de la actual instancia
     *
     * Si el número actual es N, entonces percAddMe(X) devuelve N - N*X/100.
     * Ejemplo: Si el número actual es 200, percAdd(5) calcula el 5% de 200 que es 10 y se lo suma, devolviendo 190,
     * que pasa a sustituir al número actual.
     * Esta función es útil para descuentos como rebajas de precio.
     *
     * @param mixed $x Porcentaje. Puede ser un Num o algo que entiende el constructor de Num
     * @return $this
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function percSubMe($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        $this->setVal(bcsub($this->getVal(), bcdiv(bcmul($this->getVal(), $x->getVal(), self::PRE), '100', self::PRE), self::PRE), FALSE);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Obtener el número original al que se le añadió cierto porcentaje
     *
     * Si el número actual es N, entonces percBase(X) devuelve N*100/(100+X).
     * Ejemplo: Sea el número actual 242, que es el precio final de un producto incluyendo impuestos, que sabemos
     * que son un 21%. percBase(21) nos permite averiguar el precio antes de aplicar los impuestos, que es 200.
     *
     * @param $x Porcentaje. Puede ser un Num o algo que entiende el constructor de Num
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function percBase($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        return new Num(bcdiv(bcmul('100', $this->getVal(), self::PRE), bcadd('100', $x->getVal(), self::PRE), self::PRE), FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Obtener el número original al que se le añadió cierto porcentaje
     * El resultado pasa a sustitur el valor de la actual instancia
     *
     * Si el número actual es N, entonces percBase(X) devuelve N*100/(100+X).
     * Ejemplo: Sea el número actual 242, que es el precio final de un producto incluyendo impuestos, que sabemos
     * que son un 21%. percBase(21) nos permite averiguar el precio antes de aplicar los impuestos, que es 200,
     * que pasa a sustituir al número actual.
     *
     * @param $x Porcentaje. Puede ser un Num o algo que entiende el constructor de Num
     * @return Num
     *
     * @author Jorge A. Montes Pérez <jorge@zerfrex.com>
     */
    public function percBaseMe($x)
    {
        if (!($x instanceof self)) {
            $x = new Num($x);
        }
        $this->setVal(bcdiv(bcmul('100', $this->getVal(), self::PRE), bcadd('100', $x->getVal(), self::PRE), self::PRE), FALSE);
        return $this;
    }

    // --------------------------------------------------------------------


    /**
     * Obtener la parte entera, descartando la parte decimal,
     * del número en representación interna especificado.
     *
     * @param $strNum Numero
     * @return string
     */
    static function ir_int($strNum)
    {
        $pos = strpos($strNum, '.');
        if ($pos === FALSE) {
            return $strNum;
        }
        else {
            if ($pos > 0) {
                return substr($strNum, 0, $pos);
            }
            else {
                return '';
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Retorna el valor absoluto de un numero de representación interna
     *
     * @param $strNum Número en representación interna
     * @return string El mismo número sin el signo
     */
    static function ir_abs($strNum)
    {
        if (substr($strNum, 0, 1) == '-') {
            return substr($strNum, 1);
        }
        else {
            return $strNum;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Obtener la parte fraccional, descartando la parte entera, del
     * número en representación interna especificado. Se devuelve SIN separador.
     *
     * @param $strNum Numero
     * @return string
     */
    static function ir_dec($strNum)
    {
        $pos = strpos($strNum, '.');
        if ($pos === FALSE) {
            return '';
        }
        else {
            return substr($strNum, $pos + 1);
        }
    }

    // --------------------------------------------------------------------


}
