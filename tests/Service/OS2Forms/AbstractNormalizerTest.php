<?php

namespace App\Tests\Service\OS2Forms;

use App\Exception\WebformSubmissionException;
use App\Service\DocumentUploader;
use App\Service\OS2Forms\SubmissionNormalizers\AbstractSubmissionNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use function PHPUnit\Framework\assertEquals;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AbstractNormalizerTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject & \App\Service\OS2Forms\SubmissionNormalizers\AbstractSubmissionNormalizer $normalizer;
    private \App\Service\DocumentUploader & \PHPUnit\Framework\MockObject\MockObject $mockDocumentUploader;
    private string $mockSelvbetjeningUserApiToken;
    private \Doctrine\ORM\EntityManagerInterface & \PHPUnit\Framework\MockObject\MockObject $mockEntityManager;
    private \PHPUnit\Framework\MockObject\MockObject & \Symfony\Contracts\HttpClient\HttpClientInterface $mockHttpClientInterface;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockDocumentUploader = $this->createMock(DocumentUploader::class);
        $this->mockSelvbetjeningUserApiToken = 'some_os2forms_api_token';
        $this->mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $this->mockHttpClientInterface = $this->createMock(HttpClientInterface::class);

        $this->normalizer = $this->getMockBuilder(AbstractSubmissionNormalizer::class)
            ->setConstructorArgs([$this->mockDocumentUploader, $this->mockSelvbetjeningUserApiToken, $this->mockEntityManager, $this->mockHttpClientInterface])
            ->onlyMethods(['getConfig'])
            ->getMock()
        ;
    }

    public function testSimpleStringConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $tvist1StringProperty = 'some_string_type_property';
        $os2FormsKey = 'some_key';
        $mockStringValue = 'some_string_value';

        $config = [
            $tvist1StringProperty => [
                'os2forms_key' => $os2FormsKey,
                'type' => 'string',
            ],
        ];

        $submissionData = [
            $os2FormsKey => $mockStringValue,
        ];

        $result = $this->normalizer->handleConfig($mockSender, $config, $submissionData);

        $expected = [
            $tvist1StringProperty => $mockStringValue,
        ];

        assertEquals($expected, $result);
    }

    public function testSimpleIntegerConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $tvist1IntProperty = 'some_int_type_property';
        $os2FormsKey = 'some_key';
        $mockStringIntegerValue = '5';

        $config = [
            $tvist1IntProperty => [
                'os2forms_key' => $os2FormsKey,
                'type' => 'int',
            ],
        ];

        $submissionData = [
            $os2FormsKey => $mockStringIntegerValue,
        ];

        $result = $this->normalizer->handleConfig($mockSender, $config, $submissionData);

        $expected = [
            $tvist1IntProperty => (int) $mockStringIntegerValue,
        ];

        assertEquals($expected, $result);
    }

    public function testSimpleRequiredConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $mockProperty = 'some_property';
        $os2FormsKey = 'some_key';
        $mockStringValue = 'some_string_value';
        $mockErrorMessage = 'some_excellent_error_message!';

        $config = [
            $mockProperty => [
                'os2forms_key' => $os2FormsKey,
                'type' => 'string',
                'required' => true,
                'error_message' => $mockErrorMessage,
            ],
        ];

        $submissionData = [
            $os2FormsKey => $mockStringValue,
        ];

        $result = $this->normalizer->handleConfig($mockSender, $config, $submissionData);

        $expected = [
            $mockProperty => $mockStringValue,
        ];

        assertEquals($expected, $result);
    }

    public function testMissingRequiredConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $mockProperty = 'some_property';
        $os2FormsKey = 'some_key';
        $mockErrorMessage = 'some_excellent_error_message!';

        $this->expectException(WebformSubmissionException::class);
        $this->expectExceptionMessage($mockErrorMessage);

        $config = [
            $mockProperty => [
                'os2forms_key' => $os2FormsKey,
                'type' => 'string',
                'required' => true,
                'error_message' => $mockErrorMessage,
            ],
        ];

        // Missing the $os2FormsKey.
        $submissionData = [];

        $this->normalizer->handleConfig($mockSender, $config, $submissionData);
    }

    public function testCallableConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $mockProperty = 'some_property';
        $os2FormsKey = 'some_key';
        $mockValue = 'some_mock_value';

        $config = [
            $mockProperty => [
                'value_callback' => fn (string $property, array $spec, array $submissionData, array $normalizedData, EntityManagerInterface $entityManager) => $submissionData['some_key'],
                'type' => 'string',
            ],
        ];

        $submissionData = [
            $os2FormsKey => $mockValue,
        ];

        $result = $this->normalizer->handleConfig($mockSender, $config, $submissionData);

        $expected = [
            $mockProperty => $mockValue,
        ];

        assertEquals($expected, $result);
    }

    public function testSimpleBooleanConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $mockProperty = 'some_property';
        $os2FormsKey = 'some_key';
        $mockValue = 'Ja';

        $config = [
            $mockProperty => [
                'os2forms_key' => $os2FormsKey,
                'type' => 'boolean',
            ],
        ];

        $submissionData = [
            $os2FormsKey => $mockValue,
        ];

        $result = $this->normalizer->handleConfig($mockSender, $config, $submissionData);

        $expected = [
            $mockProperty => true,
        ];

        assertEquals($expected, $result);
    }

    public function testSimpleRequiredBooleanConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $mockProperty = 'some_property';
        $os2FormsKey = 'some_key';
        $mockValue = 'Nej';

        $config = [
            $mockProperty => [
                'os2forms_key' => $os2FormsKey,
                'required' => true,
                'type' => 'boolean',
            ],
        ];

        $submissionData = [
            $os2FormsKey => $mockValue,
        ];

        $result = $this->normalizer->handleConfig($mockSender, $config, $submissionData);

        $expected = [
            $mockProperty => false,
        ];

        assertEquals($expected, $result);
    }

    public function testFaultyBooleanConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $mockProperty = 'some_property';
        $os2FormsKey = 'some_key';
        $mockValue = 'Måske';

        $this->expectException(WebformSubmissionException::class);
        $this->expectExceptionMessage(sprintf('The property value %s cannot be transformed into bool.', $mockValue));

        $config = [
            $mockProperty => [
                'os2forms_key' => $os2FormsKey,
                'type' => 'boolean',
            ],
        ];

        $submissionData = [
            $os2FormsKey => $mockValue,
        ];

        $this->normalizer->handleConfig($mockSender, $config, $submissionData);
    }

    public function testSimpleDateTimeConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $mockProperty = 'some_property';
        $os2FormsKey = 'some_key';
        $mockValue = '2022-06-14';

        $config = [
            $mockProperty => [
                'os2forms_key' => $os2FormsKey,
                'type' => 'datetime',
            ],
        ];

        $submissionData = [
            $os2FormsKey => $mockValue,
        ];

        $result = $this->normalizer->handleConfig($mockSender, $config, $submissionData);

        $expected = [
            $mockProperty => new \DateTime($mockValue),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testEmptySubmissionDateTimeConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $mockProperty = 'some_property';
        $os2FormsKey = 'some_key';
        $mockValue = '';

        $config = [
            $mockProperty => [
                'os2forms_key' => $os2FormsKey,
                'type' => 'datetime',
            ],
        ];

        $submissionData = [
            $os2FormsKey => $mockValue,
        ];

        $result = $this->normalizer->handleConfig($mockSender, $config, $submissionData);

        $expected = [
            $mockProperty => null,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testSimpleAllowedValuesConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $mockProperty = 'some_property';
        $os2FormsKey = 'some_key';
        $mockValue = 'some_value';

        $config = [
            $mockProperty => [
                'os2forms_key' => $os2FormsKey,
                'type' => 'string',
                'allowed_values' => [
                    $mockValue,
                    'some_other_value',
                ],
            ],
        ];

        $submissionData = [
            $os2FormsKey => $mockValue,
        ];

        $result = $this->normalizer->handleConfig($mockSender, $config, $submissionData);

        $expected = [
            $mockProperty => $mockValue,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testFaultyAllowedValuesConfiguration()
    {
        $mockSender = 'some_mock_sender';
        $mockProperty = 'some_property';
        $os2FormsKey = 'some_key';
        $mockValue = 'some_value';
        $mockAllowedValues = [
            'some_other_value_1',
            'some_other_value_2',
        ];

        $this->expectException(WebformSubmissionException::class);
        $expectedMessage = sprintf('Property %s value %s must be one of the following values: %s.', $mockProperty, $mockValue, implode(', ', $mockAllowedValues));
        $this->expectExceptionMessage($expectedMessage);

        $config = [
            $mockProperty => [
                'os2forms_key' => $os2FormsKey,
                'type' => 'string',
                'allowed_values' => $mockAllowedValues,
            ],
        ];

        $submissionData = [
            $os2FormsKey => $mockValue,
        ];

        $this->normalizer->handleConfig($mockSender, $config, $submissionData);
    }
}
