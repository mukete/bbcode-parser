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
use Youthweb\BBCodeParser\Definition\Url;
use Youthweb\BBCodeParser\Tests\Fixtures\MockerTrait;

class UrlTest extends \PHPUnit\Framework\TestCase
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

        $config->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['callbacks.url_content.short_url', null, false],
                        ['callbacks.url_content.short_url_length', null, 55],
                        ['callbacks.url_content.target', null, '_blank'],
                    ]
                )
            );

        $definition = new Url($config);

        $this->assertSame($expected, $definition->asHtml($elementNode));
    }

    /**
     * @test
     */
    public function testAsHtmlWithShortenLongUrl()
    {
        $text = 'http://example.org/this/is/a/very/long/url.with?query=params';
        $attribute = null;
        $expected = '<a target="_blank" href="http://example.org/this/is/a/very/long/url.with?query=params">example.org/this/i…ery=params</a>';

        $elementNode = $this->buildElementNodeMock($text, $attribute);

        $config = $this->getMockBuilder('Youthweb\BBCodeParser\Config')
            ->setMethods(['get'])
            ->getMock();

        $config->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['callbacks.url_content.short_url', null, true],
                        ['callbacks.url_content.short_url_length', null, 30],
                        ['callbacks.url_content.target', null, '_blank'],
                    ]
                )
            );

        $definition = new Url($config);

        $this->assertSame($expected, $definition->asHtml($elementNode));
    }

    /**
     * @test
     */
    public function testAsHtmlWithShortenShortUrl()
    {
        $text = 'http://example.org/this/is/a/very/long/url.with?query=params';
        $attribute = null;
        $expected = '<a target="_blank" href="http://example.org/this/is/a/very/long/url.with?query=params">example.org/this/i…</a>';

        $elementNode = $this->buildElementNodeMock($text, $attribute);

        $config = $this->getMockBuilder('Youthweb\BBCodeParser\Config')
            ->setMethods(['get'])
            ->getMock();

        $config->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['callbacks.url_content.short_url', null, true],
                        ['callbacks.url_content.short_url_length', null, 20],
                        ['callbacks.url_content.target', null, '_blank'],
                    ]
                )
            );

        $definition = new Url($config);

        $this->assertSame($expected, $definition->asHtml($elementNode));
    }

    /**
     * @test
     */
    public function testAsHtmlWithoutTarget()
    {
        $text = 'http://example.org/this/is/a/very/long/url.with?query=params';
        $attribute = null;
        $expected = '<a href="http://example.org/this/is/a/very/long/url.with?query=params">example.org/this/is/a/very/long/url.with?query=params</a>';

        $elementNode = $this->buildElementNodeMock($text, $attribute);

        $config = $this->getMockBuilder('Youthweb\BBCodeParser\Config')
            ->setMethods(['get'])
            ->getMock();

        $config->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['callbacks.url_content.short_url', null, true],
                        ['callbacks.url_content.short_url_length', null, 55],
                        ['callbacks.url_content.target', null, null],
                    ]
                )
            );

        $definition = new Url($config);

        $this->assertSame($expected, $definition->asHtml($elementNode));
    }

    /**
     * @test
     */
    public function testAsHtmlWithTargetOnYouthwebUrl()
    {
        $text = 'http://youthweb.net/this/is/a/very/long/url.with?query=params';
        $attribute = null;
        $expected = '<a href="http://youthweb.net/this/is/a/very/long/url.with?query=params">youthweb.net/this/is/a/very/long/url.with?query=params</a>';

        $elementNode = $this->buildElementNodeMock($text, $attribute);

        $config = $this->getMockBuilder('Youthweb\BBCodeParser\Config')
            ->setMethods(['get'])
            ->getMock();

        $config->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['callbacks.url_content.short_url', null, true],
                        ['callbacks.url_content.short_url_length', null, 55],
                        ['callbacks.url_content.target', null, '_blank'],
                    ]
                )
            );

        $definition = new Url($config);

        $this->assertSame($expected, $definition->asHtml($elementNode));
    }

    /**
     * @test
     */
    public function testAsHtmlWithTargetOnYouthwebUrlWithoutHttp()
    {
        $text = 'ftp://youthweb.net/this/is/a/very/long/url.with?query=params';
        $attribute = null;
        $expected = '<a target="_blank" href="ftp://youthweb.net/this/is/a/very/long/url.with?query=params">youthweb.net/this/is/a/very/long/url.with?query=params</a>';

        $elementNode = $this->buildElementNodeMock($text, $attribute);

        $config = $this->getMockBuilder('Youthweb\BBCodeParser\Config')
            ->setMethods(['get'])
            ->getMock();

        $config->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['callbacks.url_content.short_url', null, true],
                        ['callbacks.url_content.short_url_length', null, 55],
                        ['callbacks.url_content.target', null, '_blank'],
                    ]
                )
            );

        $definition = new Url($config);

        $this->assertSame($expected, $definition->asHtml($elementNode));
    }

    /**
     * data provider
     */
    public function dataProvider()
    {
        return [
            [
                'http://example.org/foo?query=string&foo=bar',
                null,
                '<a target="_blank" href="http://example.org/foo?query=string&amp;foo=bar">http://example.org/foo?query=string&foo=bar</a>',
            ],
            [
                'http://example.org',
                null,
                '<a target="_blank" href="http://example.org">http://example.org</a>',
            ],
            [
                'invalid url',
                null,
                'invalid url',
            ],
            [
                '',
                null,
                '',
            ],
        ];
    }
}
