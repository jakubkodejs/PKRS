<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Neon {


    /**
     * Simple parser & generator for Nette Object Notation.
     *
     * @author     David Grudl
     */
    class Neon_class
    {
        const BLOCK = Encoder::BLOCK;
        const CHAIN = '!!chain';


        /**
         * Returns the NEON representation of a value.
         * @param  mixed
         * @param  int
         * @return string
         */
        public static function encode($var, $options = NULL)
        {
            $encoder = new Encoder;
            return $encoder->encode($var, $options);
        }


        /**
         * Decodes a NEON string.
         * @param  string
         * @return mixed
         */
        public static function decode($input)
        {
            if (!defined("__PKRS_NEON_LOADED"))
                define("__PKRS_NEON_LOADED",true);
            $decoder = new Decoder;
            return $decoder->decode($input);
        }

    }
}

