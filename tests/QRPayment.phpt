<?php declare(strict_types=1);

/**
 * Test: JedenWeb\QRPayment\QRPayment.
 *
 * @testCase Tests\JedenWeb\QRPayment\QRPaymentTest
 * @package JedenWeb\QRPayment
 */

namespace Tests\JedenWeb\QRPayment;

require_once __DIR__ . '/bootstrap.php';

class QRPaymentTest extends \Tester\TestCase
{

	public function setUp()
	{

	}

	public function testBasic(): void
	{
		$payment = new \JedenWeb\QRPayment\QRPayment('CZ2806000000000168540115');
		$payment->setAmount('450')
			->setCurrency('CZK')
			->setMessage('PLATBA ZA ZBOZI');

		\Tester\Assert::same('e8f0bf9e', $payment->getChecksum());
		\Tester\Assert::same('SPD*1.0*ACC:CZ2806000000000168540115*AM:450.00*CC:CZK*MSG:PLATBA ZA ZBOZI*CRC32:e8f0bf9e', $payment->toString());
	}

	public function testExceptions(): void
	{
		\Tester\Assert::exception(static function (): void {
			$payment = new \JedenWeb\QRPayment\QRPayment('CZ2806000000000168540111');
		}, \JedenWeb\QRPayment\Exception\InvalidFormat::class);

		$payment = new \JedenWeb\QRPayment\QRPayment('CZ2806000000000168540115');

		\Tester\Assert::exception(static function () use ($payment): void {
			$payment->setAmount('asd');
		}, \JedenWeb\QRPayment\Exception\DisallowedCharacter::class);

		\Tester\Assert::exception(static function () use ($payment): void {
			$payment->setMessage('💩');
		}, \JedenWeb\QRPayment\Exception\DisallowedCharacter::class);

		\Tester\Assert::exception(static function () use ($payment): void {
			$payment->setAmount('14124151241213');
		}, \JedenWeb\QRPayment\Exception\AllowedLengthExceeded::class);

		\Tester\Assert::exception(static function () use ($payment): void {
			$payment->setCurrency('ASDZ');
		}, \JedenWeb\QRPayment\Exception\InvalidFormat::class);
	}

}

(new QRPaymentTest())->run();
