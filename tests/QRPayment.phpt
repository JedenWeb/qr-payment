<?php declare(strict_types=1);

/**
 * Test: JedenWeb\QRPayment\QRPayment.
 *
 * @testCase Tests\JedenWeb\QRPayment\QRPaymentTest
 * @author Pavel Jurásek
 * @package JedenWeb\QRPayment
 */

namespace Tests\JedenWeb\QRPayment;

use JedenWeb\QRPayment\AllowedLengthExceeded;
use JedenWeb\QRPayment\DisallowedCharacter;
use JedenWeb\QRPayment\InvalidFormat;
use JedenWeb\QRPayment\QRPayment;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';

/**
 * @author Pavel Jurásek
 */
class QRPaymentTest extends Tester\TestCase
{

	public function setUp()
	{

	}

	public function testBasic(): void
	{
		$payment = new QRPayment('CZ2806000000000168540115');
		$payment->setAmount('450')
			->setCurrency('CZK')
			->setMessage('PLATBA ZA ZBOZI');

		Assert::same('e8f0bf9e', $payment->getChecksum());
		Assert::same('SPD*1.0*ACC:CZ2806000000000168540115*AM:450.00*CC:CZK*MSG:PLATBA ZA ZBOZI*CRC32:e8f0bf9e', $payment->toString());
	}

	public function testExceptions(): void
	{
		Assert::exception(function () {
			$payment = new QRPayment('CZ2806000000000168540111');
		}, InvalidFormat::class);

		$payment = new QRPayment('CZ2806000000000168540115');

		Assert::exception(function () use ($payment) {
			$payment->setAmount('asd');
		}, DisallowedCharacter::class);

		Assert::exception(function () use ($payment) {
			$payment->setAmount('14124151241213');
		}, AllowedLengthExceeded::class);

		Assert::exception(function () use ($payment) {
			$payment->setCurrency('ASDZ');
		}, InvalidFormat::class);
	}

}

(new QRPaymentTest())->run();
