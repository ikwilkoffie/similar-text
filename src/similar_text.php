<?php
/**
*
* @Name : similar-text
* @Programmer : Akpé Aurelle Emmanuel Moïse Zinsou
* @Date : 2019-04-01
* @Released under : https://github.com/manuwhat/similar-text/blob/master/LICENSE
* @Repository : https://github.com/manuwhat/similar
*
**/


namespace EZAMA{
        
    class similar_text
    {
        private function __construct()
        {
        }
        public static function similarText($a, $b, $round = 2, $insensitive = true, &$stats = false, $getParts = false, $checkposition = false)
        {
            if (!is_string($a) || !is_string($b)) {
                return false;
            }
            if ($insensitive) {
                $a = self::strtolower($a);
                $b = self::strtolower($b);
            } else {
                $a = self::split($a);
                $b = self::split($b);
            }
            /* prevent bad types and useless memory usage due to for example array instead of simple boolean */
            unset($insensitive);
            $getParts = (bool) $getParts;
            /*  ******************************************************************************************** */
            $ca = count($a);
            $cb = count($b);
            if ($ca < $cb) {
                $stats = self::getStats($cb, $a, self::_check($a, $b, $getParts, $round, $checkposition), $getParts, $round);
            } else {
                $stats = self::getStats($ca, $b, self::_check($b, $a, $getParts, $round, $checkposition), $getParts, $round);
            }
            return $stats['similar'];
        }
        
        protected static function _check($a, $b, $getParts, $round, $checkposition = false)
        {
            $diff = array();
            if ($getParts) {
                $diff[] = array_diff($a, $b);
                $diff[] = array_diff($b, $a);
            }
            $diff[] = $checkposition ?array_intersect_assoc($a, $b) : array_intersect($a, $b);
            $diff[] = round(count(array_intersect(self::getParts($a, $c), self::getParts($b))) / $c * 100, $round);
            $diff[] = $a === $b;
            return $diff;
        }
        
        protected static function getStats($ca, $b, $diff, $getParts, $round)
        {
            $stats = array();
            if ($getParts) {
                $stats['similar'] = round(count($diff[2]) * 100 / $ca, $round);
                $stats['substr'] = $diff[3];
                $stats['contain'] = ($diff[2] === $b) ?true:false;
                $stats['equal'] = $diff[4];
                $stats['a-b'] = $diff[0];
                $stats['b-a'] = $diff[1];
                $stats['a&b'] = $diff[2];
            } else {
                $stats['similar'] = round(count($diff[0]) * 100 / $ca, $round);
                $stats['substr'] = $diff[1];
                $stats['contain'] = ($diff[0] === $b) ?true:false;
                $stats['equal'] = $diff[2];
            }
            return $stats;
        }

        protected static function getParts($b, &$c = 0, $lengthCapture = false)
        {
            $parts = array();
            $tmp = '';
            $c = 0;
            $length = 0;
            $lengthCapture = (bool) $lengthCapture;
            if ($lengthCapture) {
                self::capturePartsWithLength($b, $length, $tmp, $c, $parts);
            } else {
                self::capturePartsWithoutLength($b, $tmp, $c, $parts);
            }
            return $parts;
        }
        
        private static function capturePartsWithoutLength(&$b, $tmp, &$c, &$parts)
        {
            foreach ($b as $k=>$v) {
                if (ctype_space($v) || ctype_punct($v)) {
                    $parts[] = $tmp;
                    $parts[] = $v;
                    $c += 2;
                    $tmp = '';
                    continue;
                }
                $tmp .= $v;
            }
            if (!empty($tmp)) {
                $parts[] = $tmp;
                $c++;
            }
        }
        
        private static function capturePartsWithLength(&$b, $length, $tmp, &$c, &$parts)
        {
            foreach ($b as $k=>$v) {
                $length++;
                if (ctype_space($v) || ctype_punct($v)) {
                    $parts[] = array($tmp, $length - 1);
                    $parts[] = array($v, 1);
                    $c += 2;
                    $tmp = '';
                    $length = 0;
                    continue;
                }
                $tmp .= $v;
            }
            if (!empty($tmp)) {
                $parts[] = array($tmp, $length);
                $c++;
            }
        }
        
        
        
        protected static function is_ascii($str)
        {
            if ('' === $str) {
                return true;
            }

            return !preg_match('/[^\x09\x10\x13\x0A\x0D\x20-\x7E]/', $str);
        }
        
        protected static function strtolower($str)
        {
            $split = self::split($str);
            if (is_array($split)) {
                return
                    array_map(
                        function($val) {
                            if (self::is_ascii($val)) {
                                return strtolower($val);
                            }
                            return $val;
                        },
                        $split
                )
        
                            ;
            } else {
                return array();
            }
        }
        
        protected static function split($str, $grams = false)
        {
            if (!is_string($str)) {
                return array();
            }
            static $split = [];
            static $old = '';
            static $oldGrams = 1;
            $grams = is_int($grams) && $grams >= 1 && $grams <= strlen($str) ? $grams : false;
            return self::getSplit($str, $split, $old, $oldGrams, $grams);
        }
        
        private static function _split(&$str, &$split, &$old, &$oldGrams, $grams)
        {
            $old = $str;
            $oldGrams = $grams;
            $split = !$grams ? preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY) : preg_split('/(.{'.$grams.'})/su', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            return $split;
        }
        
        private static function getSplit(&$str, &$split, &$old, &$oldGrams, $grams)
        {
            if ($old === $str && $oldGrams === $grams) {
                return $split;
            } else {
                return self::_split($str, $split, $old, $oldGrams, $grams);
            }
        }
    }
    
}
