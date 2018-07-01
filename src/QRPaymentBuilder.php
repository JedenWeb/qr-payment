<?php declare(strict_types=1);

namespace JedenWeb\QRPayment;

/**
 * @author Pavel Jurásek
 */
class QRPaymentBuilder
{

	/** @var string */
	private $iban;

	/** @var string|null */
	private $amount;

	/** @var string|null */
	private $currency;

	/** @var string|null */
	private $receiverPaymentIdentificator;

	/** @var string|null */
	private $name;

	/** @var \DateTime|null */
	private $maturityDate;

	/** @var string|null */
	private $message;

	/** @var string|null */
	private $paymentType;

	/** @var string|null */
	private $notification;

	/** @var string|null */
	private $address;

	public function setIban(string $iban, ?string $bic = null): self
	{
		if (!verify_iban($iban)) {
			throw new InvalidIban(sprintf('IBAN %s is not valid.', $iban));
		}

		$this->iban = $iban;

		return $this;
	}

	public function setAmount(string $amount): self
	{
		$amount = number_format((float) $amount / 100, 2, '.', '');

		if (strlen($amount) > 10) {
			throw new AllowedLengthExceeded('Amount can be at most 10 characters long.');
		}

		$this->amount = $amount;

		return $this;
	}

	public function setCurrency(string $currency): self
	{
		if (!preg_match('~^[A-Z]{3}\z~', $currency)) {
			throw new InvalidCurrency('Currency code %s is not valid.', $currency);
		}

		$this->currency = $currency;

		return $this;
	}

	public function setReceiverPaymentIdentificator(string $identificator): self
	{
		if (strlen($identificator) > 16) {
			throw new AllowedLengthExceeded('Receiver payment identificator can be at most 16 characters long.');
		}

		$this->receiverPaymentIdentificator = $identificator;

		return $this;
	}

	public function setReceiverName(string $name): self
	{
		if (strlen($name) > 35) {
			throw new AllowedLengthExceeded('Receiver name can be at most 35 characters long.');
		} elseif (!preg_match('~^[0-9A-Z \$%\*\+\-\.\/:]\z~', $name)) {
			throw new DisallowedCharacter('Field can contain only these characters: 0-9, A-Z, (space), $, %, *, +, -, ., /, :');
		}

		$this->name = $name;

		return $this;
	}

	public function setMaturityDate(\DateTime $date): self
	{
		$this->maturityDate = $date;

		return $this;
	}

	public function setPaymentType(string $type): self
	{
		if (strlen($type) > 3) {
			throw new AllowedLengthExceeded('Payment type can be at most 3 characters long.');
		} elseif (!preg_match('~^[0-9A-Z \$%\*\+\-\.\/:]\z~', $type)) {
			throw new DisallowedCharacter('Field can contain only these characters: 0-9, A-Z, (space), $, %, *, +, -, ., /, :');
		}

		$this->paymentType = $type;

		return $this;
	}

	public function setMessage(string $message): self
	{
		if (strlen($message) > 60) {
			throw new AllowedLengthExceeded('Message can be at most 60 characters long.');
		} elseif (!preg_match('~^[0-9A-Z \$%\*\+\-\.\/:]\z~', $message)) {
			throw new DisallowedCharacter('Field can contain only these characters: 0-9, A-Z, (space), $, %, *, +, -, ., /, :');
		}

		$this->message = $message;

		return $this;
	}

	public function setNotification(string $channel, string $address): self
	{
		if ($channel !== 'P' && $channel !== 'E') {
			throw new DisallowedCharacter('Notification can be either P(hone) or E(-mail).');
		}

		if ($channel === 'P' && !preg_match('~^(\+|00)[0-9]{2,3}[0,9]{0,12}\z~', $address)) {
			throw new InvalidFormat('Phone number format is not valid.');
		}

		$this->notification = $channel;
		$this->address = $address;

		return $this;
	}

	public function build(): QRPayment
	{
		$fields = [
			'ACC' => $this->iban,
		];

		if ($this->amount) {
			$fields['AM'] = $this->amount;
		}

		if ($this->currency) {
			$fields['CC'] = $this->currency;
		}

		if ($this->receiverPaymentIdentificator) {
			$fields['RF'] = $this->receiverPaymentIdentificator;
		}

		if ($this->name) {
			$fields['RN'] = $this->name;
		}

		if ($this->maturityDate) {
			$fields['DT'] = $this->maturityDate->format('Ymd');
		}

		if ($this->paymentType) {
			$fields['PT'] = $this->paymentType;
		}

		if ($this->message) {
			$fields['MSG'] = $this->message;
		}

		if ($this->notification) {
			$fields['NT'] = $this->notification;
			$fields['NTA'] = $this->address;
		}

		return new QRPayment();
	}

}
