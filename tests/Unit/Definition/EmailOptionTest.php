<?php
/*
 * This file is part of the Youthweb\BBCodeParser package.
 *
 * Copyright (C) 2016-2018  Youthweb e.V. <info@youthweb.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Youthweb\BBCodeParser\Tests\Unit\Definition;

use JBBCode\ElementNode;
use JBBCode\TextNode;
use Youthweb\BBCodeParser\Definition\EmailOption;
use Youthweb\BBCodeParser\Tests\Fixtures\MockerTrait;

class EmailOptionTest extends \PHPUnit\Framework\TestCase
{
    use MockerTrait;

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $text
     * @param mixed $attribute
     * @param mixed $expected
     */
    public function testAsHtml($text, $attribute, $expected)
    {
        $elementNode = $this->buildElementNodeMock($text, $attribute);

        $config = $this->getMockBuilder('Youthweb\BBCodeParser\Config')
            ->setMethods(['get'])
            ->getMock();

        $config->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['cacheitempool', null, null],
                        ['callbacks.email_content.protect_email', null, false],
                    ]
                )
            );

        $definition = new EmailOption($config);

        $this->assertSame($expected, $definition->asHtml($elementNode));
    }

    /**
     * @dataProvider dataProviderProtectedEmail
     *
     * @param mixed $text
     * @param mixed $attribute
     * @param mixed $expected
     */
    public function testAsHtmlWithProtectedEmail($text, $attribute, $expected)
    {
        $elementNode = $this->buildElementNodeMock($text, $attribute);

        $config = $this->getMockBuilder('Youthweb\BBCodeParser\Config')
            ->setMethods(['get'])
            ->getMock();

        $config->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['cacheitempool', null, null],
                        ['callbacks.email_content.protect_email', null, true],
                    ]
                )
            );

        $definition = new EmailOption($config);

        $this->assertSame($expected, $definition->asHtml($elementNode));
    }

    /**
     * data provider
     */
    public function dataProvider()
    {
        return [
            [
                'email',
                'mail@example.org',
                '<a href="mailto:mail@example.org">email</a>',
            ],
            [
                'email',
                ['mail@example.org'],
                '<a href="mailto:mail@example.org">email</a>',
            ],
            [
                'text',
                'invalid email',
                'text',
            ],
            [
                '',
                'mail@example.org',
                '<a href="mailto:mail@example.org">mail@example.org</a>',
            ],
        ];
    }

    /**
     * data provider for protected emails
     */
    public function dataProviderProtectedEmail()
    {
        return [
            [
                'email',
                'mail@example.org',
                '<script type="text/javascript">(function() {var user = "mail";var at = "@";var server = "example.org";document.write(\'<a href="\' + \'mail\' + \'to:\' + user + at + server + \'">email</a>\');})();</script>',
            ],
            [
                'email',
                ['mail@example.org'],
                '<script type="text/javascript">(function() {var user = "mail";var at = "@";var server = "example.org";document.write(\'<a href="\' + \'mail\' + \'to:\' + user + at + server + \'">email</a>\');})();</script>',
            ],
            [
                'text',
                'invalid email',
                'text',
            ],
            [
                '',
                'mail@example.org',
                '<script type="text/javascript">(function() {var user = "mail";var at = "@";var server = "example.org";document.write(\'<a href="\' + \'mail\' + \'to:\' + user + at + server + \'">mail[at]example.org</a>\');})();</script>',
            ],
        ];
    }
}
