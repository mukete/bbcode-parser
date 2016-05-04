<?php

namespace Youthweb\BBCodeParser\Tests\Unit;

use Youthweb\BBCodeParser\Tests\Fixtures\MockHttpStreamWrapper;
use Youthweb\BBCodeParser\Tests\Fixtures\ValidationMock;
use Youthweb\BBCodeParser\Validation;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		stream_wrapper_unregister('http');
		stream_wrapper_register(
			'http',
			'Youthweb\BBCodeParser\Tests\Fixtures\MockHttpStreamWrapper'
		) or die('Failed to register protocol');
	}

	public function tearDown()
	{
		stream_wrapper_restore('http');

		ValidationMock::resetImageCounter();
	}

	/**
	 * @test
	 */
	public function testValidImageUrl()
	{
		MockHttpStreamWrapper::$mockMetaData = [
			'Content-Type: image/jpeg',
		];
		MockHttpStreamWrapper::$mockResponseCode = 'HTTP/1.1 200 OK';

		ValidationMock::resetImageCounter();

		$validation = new Validation();

		$this->assertTrue($validation->isValidImageUrl('http://example.org/image.jpg', true));
	}

	/**
	 * @test
	 */
	public function testMultipleValidImageUrl()
	{
		MockHttpStreamWrapper::$mockMetaData = [
			'Content-Type: image/jpeg',
		];
		MockHttpStreamWrapper::$mockResponseCode = 'HTTP/1.1 200 OK';

		ValidationMock::resetImageCounter();

		$validation = new Validation();

		$this->assertTrue($validation->isValidImageUrl('http://example.org/image.jpg', true));
		$this->assertFalse($validation->isValidImageUrl('http://example.org/image2.jpg', true));
	}

	/**
	 * @test
	 */
	public function testCachedMultipleValidImageUrl()
	{
		MockHttpStreamWrapper::$mockMetaData = [
			'Content-Type: image/jpeg',
		];
		MockHttpStreamWrapper::$mockResponseCode = 'HTTP/1.1 200 OK';

		ValidationMock::resetImageCounter();

		$validation = new Validation();

		$this->assertTrue($validation->isValidImageUrl('http://example.org/image.jpg', true));
		$this->assertTrue($validation->isValidImageUrl('http://example.org/image.jpg', true));
	}

	/**
	 * @test
	 */
	public function testValidImageUrlForceless()
	{
		$validation = new Validation();

		$this->assertTrue($validation->isValidImageUrl('http://example.org/image.jpg', false));
	}

	/**
	 * @test
	 */
	public function testValidImageUrlForcelessWithInvalidUrl()
	{
		$validation = new Validation();

		$this->assertFalse($validation->isValidImageUrl('foobar', false));
	}
}
