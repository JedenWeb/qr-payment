<?php declare(strict_types=1);

/**
 * Test: JedenWeb\QRPayment\QRGenerator.
 *
 * @testCase Tests\JedenWeb\QRPayment\QRGeneratorTest
 * @package JedenWeb\QRPayment
 */

namespace Tests\JedenWeb\QRPayment;

require_once __DIR__ . '/bootstrap.php';

class QRGeneratorTest extends \Tester\TestCase
{

	public function testBasic(): void
	{
		$generator = new \JedenWeb\QRPayment\QRGenerator();

		$str = $generator->createFromString('SPD*1.0*ACC:CZ2806000000000168540115*AM:450.00*CC:CZK*MSG:PLATBA ZA ZBOZI*CRC32:e8f0bf9e');
		\Tester\Assert::equal(\file_get_contents(__DIR__ . '/output.png'), $str);
	}

}

(new QRGeneratorTest())->run();
