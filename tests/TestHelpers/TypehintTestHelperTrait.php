<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\TestHelpers;

trait TypehintTestHelperTrait
{
    public function setExpectedTypehintError()
    {
        if (!$this instanceof \PHPUnit_Framework_TestCase) {
            throw new \LogicException(
                sprintf(
                    '%s must implement \PHPUnit_Framework_TestCase to use %s',
                    get_class($this),
                    __TRAIT__
                )
            );
        }

        /**
         * @var \PHPUnit_Framework_TestCase $this
         */
        if (PHP_MAJOR_VERSION < 7) {
            /*
             * For explanation refer to links below:
             * - http://stackoverflow.com/questions/25570786/how-to-unit-test-type-hint-with-phpunit
             * - https://github.com/sebastianbergmann/phpunit/issues/178
             */
            $this->setExpectedException(get_class(new \PHPUnit_Framework_Error("", 0, "", 1)));

        } else {
            $this->setExpectedException('\TypeError');
        }
    }
}
