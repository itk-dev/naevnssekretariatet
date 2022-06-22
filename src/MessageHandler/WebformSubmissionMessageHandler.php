<?php

namespace App\MessageHandler;

use App\Message\NewWebformSubmissionMessage;
use App\Service\CaseManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebformSubmissionMessageHandler implements MessageHandlerInterface
{
    public function __construct(private CaseManager $caseManager, private HttpClientInterface $client, private $selvbetjeningUserApiToken)
    {
    }

    public function __invoke(NewWebformSubmissionMessage $message)
    {
        $dataArray = $message->getWebformSubmission();

        // Example data.
        // {
        //  "data":{
        //      "webform":{
        //          "id":"tvist_opret_sag"
        //      },
        //      "submission":{
        //          "uuid":"eb994d46-7724-48b5-ab45-f76ffb4cdf0f"
        //      }
        //  },
        //  "links":{
        //      "sender":"http:\/\/0.0.0.0:53779\/",
        //      "get_submission_url":"http:\/\/0.0.0.0:53779\/webform_rest\/tvist_opret_sag\/submission\/eb994d46-7724-48b5-ab45-f76ffb4cdf0f"
        //  }
        //}

        if (isset($dataArray['links']['get_submission_url']) && is_string($dataArray['links']['get_submission_url'])
            && isset($dataArray['data']['webform']['id']) && is_string($dataArray['data']['webform']['id'])
        ) {
            $getSubmissionUrl = $dataArray['links']['get_submission_url'];
            $sender = $dataArray['links']['sender'];
            $webformId = $dataArray['data']['webform']['id'];

            $response = $this->client->request(
                'GET',
                $getSubmissionUrl,
                [
                    'headers' => [
                        'api-key: '.$this->selvbetjeningUserApiToken,
                    ],
                ]
            );

            $content = json_decode($response->getContent(), true);

            $submissionData = $content['data'];

            $this->caseManager->handleOS2FormsSubmission($webformId, $sender, $submissionData);
        } else {
            $message = sprintf('Webform submission data must contain both a submission url and a webform id.');
            throw new \InvalidArgumentException($message, 400);
        }
    }
}
