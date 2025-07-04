<?php declare(strict_types=1);

namespace JedenWeb\QRPayment;

class QRGenerator
{

	/**
	 * @throws \Endroid\QrCode\Exception\ValidationException
	 */
	public function create(\JedenWeb\QRPayment\QRPayment $payment, ?\Endroid\QrCode\Builder\Builder $builder = null): string
	{
		return $this->createFromString($payment->toString(), $builder);
	}

	/**
	 * @throws \Endroid\QrCode\Exception\ValidationException
	 */
	public function createFromString(string $data, ?\Endroid\QrCode\Builder\Builder $builder = null): string
	{
		$builder = $builder ?? $this->defaultBuilder();

		return $builder
			->build(data: $data)
			->getString()
		;
	}

	protected function defaultBuilder(): \Endroid\QrCode\Builder\Builder
	{
		return new \Endroid\QrCode\Builder\Builder(
			new \Endroid\QrCode\Writer\PngWriter(),
			errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Medium,
			size: 300,
			margin: 10,
			backgroundColor: new \Endroid\QrCode\Color\Color(255, 255, 255, 127),
		);
	}

}
