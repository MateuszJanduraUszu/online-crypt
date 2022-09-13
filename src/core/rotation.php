<?php declare(strict_types = 1);
// rotation.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    interface _Integer_rotation_base { // base class for left/right rotation
        static function left(int $_Val, int $_Rotation) : int;
        static function right(int $_Val, int $_Rotation) : int;
    }

    class int8_rotation implements _Integer_rotation_base { // left/right rotation for 1-byte integer
        const digits = 8;
        const max    = 0xFF;

        static function left(int $_Val, int $_Rotation) : int {
            $_Rest = $_Rotation % self::digits;
            if ($_Rest == 0) { // no rotation
                return $_Val & self::max; // trim to 8 bits
            } else if ($_Rest >= 0) { // rotate non-negative value
                return (($_Val << $_Rest) | ($_Val >> (self::digits - $_Rest))) & self::max; // trim to 8 bits
            } else { // must be non-negative
                return self::right($_Val, -$_Rest);
            }
        }

        static function right(int $_Val, int $_Rotation) : int {
            $_Rest = $_Rotation % self::digits;
            if ($_Rest == 0) { // no rotation
                return $_Val & self::max; // trim to 8 bits
            } else if ($_Rest >= 0) { // rotate non-negative value
                return (($_Val >> $_Rest) | ($_Val << (self::digits - $_Rest))) & self::max; // trim to 8 bits
            } else { // must be non-negative
                return self::left($_Val, -$_Rest);
            }
        }
    }

    class int16_rotation implements _Integer_rotation_base { // left/right  rotation for 2-byte integer
        const digits = 16;
        const max    = 0xFFFF;

        static function left(int $_Val, int $_Rotation) : int {
            $_Rest = $_Rotation % self::digits;
            if ($_Rest == 0) { // no rotation
                return $_Val & self::max; // trim to 16 bits
            } else if ($_Rest >= 0) { // rotate non-negative value
                return (($_Val << $_Rest) | ($_Val >> (self::digits - $_Rest))) & self::max; // trim to 16 bits
            } else { // must be non-negative
                return self::right($_Val, -$_Rest);
            }
        }

        static function right(int $_Val, int $_Rotation) : int {
            $_Rest = $_Rotation % self::digits;
            if ($_Rest == 0) { // no rotation
                return $_Val & self::max; // trim to 16 bits
            } else if ($_Rest >= 0) { // rotate non-negative value
                return (($_Val >> $_Rest) | ($_Val << (self::digits - $_Rest))) & self::max; // trim to 16 bits
            } else { // must be non-negative
                return self::left($_Val, -$_Rest);
            }
        }
    }

    class int32_rotation implements _Integer_rotation_base { // left/right bits rotation for 4-byte integer
        const digits = 32;
        const max    = 0xFFFFFFFF;

        static function left(int $_Val, int $_Rotation) : int {
            $_Rest = $_Rotation % self::digits;
            if ($_Rest == 0) { // no rotation
                return $_Val & self::max; // trim to 32 bits
            } else if ($_Rest >= 0) { // rotate non-negative value
                return (($_Val << $_Rest) | ($_Val >> (self::digits - $_Rest))) & self::max; // trim to 32 bits
            } else { // must be non-negative
                return self::right($_Val, -$_Rest);
            }
        }

        static function right(int $_Val, int $_Rotation) : int {
            $_Rest = $_Rotation % self::digits;
            if ($_Rest == 0) { // no rotation
                return $_Val & self::max; // trim to 32 bits
            } else if ($_Rest >= 0) { // rotate non-negative value
                return (($_Val >> $_Rest) | ($_Val << (self::digits - $_Rest))) & self::max; // trim to 32 bits
            } else { // must be non-negative
                return self::left($_Val, -$_Rest);
            }
        }
    }

    class int64_rotation implements _Integer_rotation_base { // left/right bits rotation for 8-byte integer
        const digits = 64;
        const max    = 0xFFFFFFFFFFFFFFFF;

        static function left(int $_Val, int $_Rotation) : int {
            $_Rest = $_Rotation % self::digits;
            if ($_Rest == 0) { // no rotation
                return $_Val & self::max; // trim to 64 bits
            } else if ($_Rest >= 0) { // rotate non-negative value
                return (($_Val << $_Rest) | ($_Val >> (self::digits - $_Rest))) & self::max; // trim to 64 bits
            } else { // must be non-negative
                return self::right($_Val, -$_Rest);
            }
        }

        static function right(int $_Val, int $_Rotation) : int {
            $_Rest = $_Rotation % self::digits;
            if ($_Rest == 0) { // no rotation
                return $_Val & self::max; // trim to 64 bits
            } else if ($_Rest >= 0) { // rotate non-negative value
                return (($_Val >> $_Rest) | ($_Val << (self::digits - $_Rest))) & self::max; // trim to 64 bits
            } else { // must be non-negative
                return self::left($_Val, -$_Rest);
            }
        }
    }
} // namespace mjx
?>