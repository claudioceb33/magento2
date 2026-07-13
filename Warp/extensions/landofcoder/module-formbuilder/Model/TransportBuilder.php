<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Model;

use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder as CoreTransportBuilder;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;
use Laminas\Mime\PartFactory as MimePartFactory;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\MessageFactory as MimeMessageFactory;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Phrase;

class TransportBuilder extends CoreTransportBuilder
{
    /**
     * @var array
     */
    private array $parts = [];

    /**
     * @var MimeMessageFactory
     */
    private MimeMessageFactory $mimeMessageFactory;

    /**
     * @var MimePartFactory
     */
    private MimePartFactory $mimePartFactory;

    /**
     * @var
     */
    protected $subject;

    /**
     * @var string
     */
    protected string $content = "";

    /**
     * @var array
     */
    private array $messageData = [];

    /**
     * @var EmailMessageInterfaceFactory
     */
    private mixed $emailMessageInterfaceFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private mixed $mimeMessageInterfaceFactory;

    /**
     * @var MimePartInterfaceFactory
     */
    private mixed $mimePartInterfaceFactory;

    /**
     * @var AddressConverter|null
     */
    private mixed $addressConverter;

    /**
     * @var array
     */
    protected array $templateData = [];

    /**
     * @var string
     */
    protected string $messageType = '';

    /**
     * Message
     *
     * @var \Magento\Framework\Mail\Message
     */
    protected $message;

    /**
     * TransportBuilder constructor.
     * @param FactoryInterface $templateFactory
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     * @param ObjectManagerInterface $objectManager
     * @param TransportInterfaceFactory $mailTransportFactory
     * @param MimePartFactory $mimePartFactory
     * @param MimeMessageFactory $mimeMessageFactory
     * @param MessageInterfaceFactory|null $messageFactory
     * @param EmailMessageInterfaceFactory|null $emailMessageInterfaceFactory
     * @param MimeMessageInterfaceFactory|null $mimeMessageInterfaceFactory
     * @param MimePartInterfaceFactory|null $mimePartInterfaceFactory
     * @param AddressConverter|null $addressConverter
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MimePartFactory $mimePartFactory,
        MimeMessageFactory $mimeMessageFactory,
        MessageInterfaceFactory $messageFactory = null,
        EmailMessageInterfaceFactory $emailMessageInterfaceFactory = null,
        MimeMessageInterfaceFactory $mimeMessageInterfaceFactory = null,
        MimePartInterfaceFactory $mimePartInterfaceFactory = null,
        AddressConverter $addressConverter = null
    ) {
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory,
            $messageFactory,
            $emailMessageInterfaceFactory,
            $mimeMessageInterfaceFactory,
            $mimePartInterfaceFactory,
            $addressConverter
        );
        $this->message = $message;
        $this->mimePartFactory = $mimePartFactory;
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory ?: $this->objectManager
            ->get(EmailMessageInterfaceFactory::class);
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory ?: $this->objectManager
            ->get(MimeMessageInterfaceFactory::class);
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory ?: $this->objectManager
            ->get(MimePartInterfaceFactory::class);
        $this->addressConverter = $addressConverter ?: $this->objectManager
            ->get(AddressConverter::class);
    }

    /**
     * Set template data
     *
     * @param array $data
     * @return $this
     */
    public function setTemplateData(array $data): static
    {
        $this->templateData = $data;
        return $this;
    }

    /**
     * @param $messageType
     * @return $this
     */
    public function setMessageType($messageType): static
    {
        $this->messageType = $messageType;
        return $this;
    }

    public function addAttachment(
        $body,
        $filename = null,
        $mimeType = Mime::TYPE_OCTETSTREAM,
        $disposition = Mime::DISPOSITION_ATTACHMENT,
        $encoding = Mime::ENCODING_BASE64
    ): static {
        $this->parts[] = $this->createMimePart($body, $mimeType, $disposition, $encoding, $filename);
        return $this;
    }

    /**
     * @return $this|TransportBuilder
     * @throws LocalizedException
     */
    protected function prepareMessage(): TransportBuilder|static
    {
        $template = $this->getTemplate();
        $content = $template->processTemplate();
        $part['type'] = match ($template->getType()) {
            TemplateTypesInterface::TYPE_TEXT => MimeInterface::TYPE_TEXT,
            TemplateTypesInterface::TYPE_HTML => MimeInterface::TYPE_HTML,
            default => throw new LocalizedException(
                new Phrase('Unknown template type')
            ),
        };
        $mimePart = $this->mimePartInterfaceFactory->create(['content' => $content]);
        $parts = [$mimePart];
        $parts = array_merge($parts, $this->parts);
        $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(
            ['parts' => $parts]
        );

        $this->messageData['subject'] = html_entity_decode(
            (string)$template->getSubject(),
            ENT_QUOTES
        );
        $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);
        $this->subject = $template->getSubject();
        $this->content = $content;
        return $this;
    }

    /**
     * Get message content
     *
     * @return string
     */
    public function getMessageContent(): string
    {
        $content = "";
        if ($this->content instanceof \Laminas\Mime\Message) {
            $content = $content->generateMessage();
            $content = is_object($content) ? $content->getBodyText(true) : $content;
        } elseif ($this->content instanceof \Laminas\Mime\Part) {
            $content = $this->content->getRawContent();
        } else {
            if (is_object($this->content)) {
                $content = $this->content->generateMessage();
                $content = is_object($content) ? $content->getBodyText(true) : $content;
            } else {
                $content = $this->content;
            }

        }
        return $content;
    }

    /**
     * @param $content
     * @param string $type
     * @param string $disposition
     * @param string $encoding
     * @param null $filename
     * @return MimePart
     */
    private function createMimePart(
        $content,
        string $type = Mime::TYPE_OCTETSTREAM,
        string $disposition = Mime::DISPOSITION_ATTACHMENT,
        string $encoding = Mime::ENCODING_BASE64,
        $filename = null
    ): MimePart {
        /** @var MimePart $mimePart */
        $mimePart = $this->mimePartFactory->create();
        $mimePart->setContent($content);
        $mimePart->setType($type);
        $mimePart->setDisposition($disposition);
        $mimePart->setEncoding($encoding);

        if ($filename) {
            $mimePart->setFileName($filename);
        }

        return $mimePart;
    }

    /**
     * @param $message
     * @return \Magento\Framework\Mail\MimeMessage|MimeMessage
     */
    private function getMimeMessage($message): MimeMessage|\Magento\Framework\Mail\MimeMessage
    {
        $body = $message->getBody();

        if ($body instanceof MimeMessage || $body instanceof \Magento\Framework\Mail\MimeMessage) {
            return $body;
        }

        /** @var MimeMessage $mimeMessage */
        $mimeMessage = $this->mimeMessageFactory->create();

        if ($body) {
            $mimePart = ($body instanceof \Magento\Framework\Mail\MimeMessage)
                ? $body
                : $this->createMimePart((string)$body, Mime::TYPE_HTML, Mime::DISPOSITION_INLINE);

            $mimeMessage->setParts([$mimePart]);
        }

        return $mimeMessage;
    }

    /**
     * Add cc address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return $this
     */
    public function addCc($address, $name = ''): static
    {
        $this->addAddressByType('cc', $address, $name);

        return $this;
    }

    /**
     * Add to address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addTo($address, $name = ''): static
    {
        $this->addAddressByType('to', $address, $name);

        return $this;
    }

    /**
     * Add bcc address
     *
     * @param array|string $address
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addBcc($address): static
    {
        $this->addAddressByType('bcc', $address);

        return $this;
    }

    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param string|null $name
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setReplyTo($email, $name = null): static
    {
        $this->addAddressByType('replyTo', $email, $name);

        return $this;
    }

    /**
     * Set mail from address
     *
     * @param string|array $from
     *
     * @return $this
     * @throws InvalidArgumentException
     * @throws MailException
     * @see setFromByScope()
     *
     * @deprecated 102.0.1 This function sets the from address but does not provide
     * a way of setting the correct from addresses based on the scope.
     */
    public function setFrom($from): static
    {
        return $this->setFromByScope($from);
    }

    /**
     * Set mail from address by scopeId
     *
     * @param string|array $from
     * @param string|int $scopeId
     *
     * @return $this
     * @throws InvalidArgumentException
     * @throws MailException
     * @since 102.0.1
     */
    public function setFromByScope($from, $scopeId = null): static
    {
        $result = $this->_senderResolver->resolve($from, $scopeId);
        $this->addAddressByType('from', $result['email'], $result['name']);

        return $this;
    }

    /**
     * Set template identifier
     *
     * @param string $templateIdentifier
     *
     * @return $this
     */
    public function setTemplateIdentifier($templateIdentifier): static
    {
        $this->templateIdentifier = $templateIdentifier;

        return $this;
    }

    /**
     * Set template model
     *
     * @param string $templateModel
     *
     * @return $this
     */
    public function setTemplateModel($templateModel): static
    {
        $this->templateModel = $templateModel;
        return $this;
    }

    /**
     * Set template vars
     *
     * @param array $templateVars
     *
     * @return $this
     */
    public function setTemplateVars($templateVars): static
    {
        $this->templateVars = $templateVars;

        return $this;
    }

    /**
     * Set template options
     *
     * @param array $templateOptions
     * @return $this
     */
    public function setTemplateOptions($templateOptions): static
    {
        $this->templateOptions = $templateOptions;

        return $this;
    }

    /**
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        try {
            $this->prepareMessage();
            $mailTransport = $this->mailTransportFactory->create(['message' => clone $this->message]);
        } finally {
            $this->reset();
        }

        return $mailTransport;
    }

    /**
     * @return $this|TransportBuilder
     */
    protected function reset(): TransportBuilder|static
    {
        $this->messageData = [];
        $this->templateIdentifier = null;
        $this->templateVars = null;
        $this->templateOptions = null;
        return $this;
    }

    /**
     * @return TemplateInterface
     */
    protected function getTemplate(): TemplateInterface
    {
        return $this->templateFactory->get($this->templateIdentifier, $this->templateModel)
            ->setVars($this->templateVars)
            ->setOptions($this->templateOptions);
    }

    /**
     * @param string $addressType
     * @param array|string $email
     * @param string|null $name
     */
    private function addAddressByType(string $addressType, array|string $email, ?string $name = null): void
    {
        if (is_string($email)) {
            $this->messageData[$addressType][] = $this->addressConverter->convert($email, $name);
            return;
        }
        $convertedAddressArray = $this->addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        }
    }
}
