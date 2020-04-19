<?php
// @phpcsSniff CodeQuality.NestingLevel

// @phpcsErrorOnNextLine
function a()
{
    if (1) {
        if (2) {
            if (3) {
                if (4) {
                    if (5) {
                        echo 'Error';
                    }
                }
            }
        }
    }
}

function ok()
{
    if (1) {
        if (2) {
            echo 'ok';
        }
    }
}

// @phpcsWarningOnNextLine
function b()
{
    if (1) {
        if (2) {
            if (3) {
                echo 'Warning';
            }
        }
    }
}

function tryCatch()
{
    try {
        if (1) {
            if (2) {
                return false;
            }

            return false;
        }
    } catch (\Exception $exception) {
        return false;
    }

    if (1) {
        if (2) {
            return false;
        }

        return false;
    }

    return false;
}

function tryCatchCatch()
{
    try {
        echo 'foo';
    } catch (\RuntimeException $run) {

    } catch (\LogicException $logic) {
        if (1) {
            if (2) {
                return false;
            }
        }

        return true;
    }

    return false;
}

// @phpcsErrorOnNextLine
function tryCatchCatchFinallyError()
{
    try {
        echo 'foo';
    } catch (\RuntimeException $run) {

    } catch (\LogicException $logic) {
        if (1) {
            if (2) {
                return false;
            }
        }

        return true;
    } finally {
        if (1) {
            if (2) {
                if (3) {
                    if (4) {
                        if (5) {
                            return false;
                        }
                    }
                }
            }
        }
    }

    return false;
}

function tryFinallyOkOne()
{
    try {
        echo "x";
    } finally {
        if (1) {
            if (2) {
                return false;
            }
        }

        return true;
    }
}

function tryFinallyOkTwo()
{
    try {
        if (1) {
            if (2) {
                return false;
            }
        }
    } finally {
        return false;
    }
}

// @phpcsWarningOnNextLine
function tryFinallyMeh()
{
    try {
        if (1) {
            if (2) {
                if (3) {
                    return false;
                }
            }
        }
    } finally {
        return false;
    }
}

function tryCatchFinally()
{
    try {
        if (1) {
            if (2) {
                return false;
            }

            return false;
        }
    } catch (\Exception $exception) {
        if (1) {
            if (2) {
                return false;
            }

            return false;
        }
    } finally {
        if (1) {
            if (2) {
                return false;
            }

            return false;
        }
    }

    if (1) {
        if (2) {
            return false;
        }

        return false;
    }

    return false;
}

// @phpcsWarningOnNextLine
function c()
{
    if (1) {
        if (2) {
            if (3) {
                if (4) {
                    echo 'Warning';
                }
            }
        }
    }
}

// @phpcsErrorOnNextLine
function d()
{
    if (1) {
        if (2) {
            if (3) {
                if (4) {
                    if (5) {
                        if (6) {
                            echo 'Error';
                        }
                    }
                }
            }
        }
    }
}

class Foo {

    // @phpcsWarningOnNextLine
    public function a()
    {
        if (1) {
            if (2) {
                if (3) {
                    if (4) {
                        echo 'Warning';
                    }
                }
            }
        }
    }

    // @phpcsErrorOnNextLine
    private function b()
    {
        if (1) {
            if (2) {
                if (3) {
                    if (4) {
                        if (5) {
                            echo 'Error';
                        }
                    }
                }
            }
        }
    }

    protected function tryCatchFinallyOk()
    {
        try {
            if (1) {
                if (2) {
                    return false;
                }

                return false;
            }
        } catch (\Exception $exception) {
            if (1) {
                if (2) {
                    return false;
                }

                return false;
            }
        } finally {
            if (1) {
                if (2) {
                    return false;
                }

                return false;
            }
        }

        if (1) {
            if (2) {
                return false;
            }

            return false;
        }

        return false;
    }

    // @phpcsWarningOnNextLine
    public static function tryCatchFinallyWarningInside()
    {
        try {
            if (1) {
                if (2) {
                    if (3) {
                        echo 'Warning';
                    }

                    return false;
                }

                return false;
            }
        } catch (\Exception $exception) {
            if (1) {
                if (2) {
                    return false;
                }

                return false;
            }
        } finally {
            return false;
        }
    }

    // @phpcsErrorOnNextLine
    public static function tryCatchFinallyErrorInside()
    {
        try {
            echo 'foo';
        } catch (\Exception $exception) {
            if (1) {
                if (2) {
                    if (3) {
                        if (4) {
                            if (5) {
                                echo 'Error';
                            }
                        }
                    }

                    return false;
                }

                return false;
            }
        } finally {
            if (1) {
                if (2) {
                    return false;
                }

                return false;
            }
        }

        if (1) {
            if (2) {
                return false;
            }

            return false;
        }

        return false;
    }

    // @phpcsWarningOnNextLine
    private static function tryCatchFinallyWarningOutside()
    {
        try {
            if (1) {
                if (2) {
                    return false;
                }

                return false;
            }
        } catch (\Exception $exception) {
            if (1) {
                if (2) {
                    return false;
                }

                return false;
            }
        } finally {
            if (1) {
                if (2) {
                    return false;
                }

                return false;
            }
        }

        if (1) {
            if (2) {
                if (3) {
                    if (4) {
                        echo 'Warning';
                    }
                }
                return false;
            }

            return false;
        }

        return false;
    }

    // @phpcsErrorOnNextLine
    private static function tryCatchFinallyErrorOutside()
    {
        try {
            if (1) {
                if (2) {
                    echo 'Error';

                    return false;
                }

                return false;
            }
        } catch (\Exception $exception) {
            if (1) {
                if (2) {
                    return false;
                }

                return false;
            }
        } finally {
            if (1) {
                if (2) {
                    return false;
                }

                return false;
            }
        }

        if (1) {
            if (2) {
                if (3) {
                    if (4) {
                        if (5) {
                            echo 'Error';
                        }
                    }
                }
                return false;
            }

            return false;
        }

        return false;
    }
}
