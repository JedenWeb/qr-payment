<?php declare(strict_types=1);

/**
 * Test: JedenWeb\QRPayment\QRGenerator.
 *
 * @testCase Tests\JedenWeb\QRPayment\QRGeneratorTest
 * @author Pavel Jurásek
 * @package JedenWeb\QRPayment
 */

namespace Tests\JedenWeb\QRPayment;

use JedenWeb\QRPayment\QRGenerator;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';

/**
 * @author Pavel Jurásek
 */
class QRGeneratorTest extends Tester\TestCase
{

	public function setUp()
	{

	}

	public function testBasic()
	{
		$generator = new QRGenerator;

		$str = $generator->createFromString('SPD*1.0*ACC:CZ2806000000000168540115*AM:450.00*CC:CZK*MSG:PLATBA ZA ZBOZI*CRC32:e8f0bf9e');
		Assert::equal(\file_get_contents(__DIR__ . '/output.png'), $str);
	}

}

(new QRGeneratorTest())->run();
