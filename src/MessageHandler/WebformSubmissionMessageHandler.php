<?php

namespace App\MessageHandler;

use App\Exception\WebformSubmissionException;
use App\Message\NewWebformSubmissionMessage;
use App\Repository\UserRepository;
use App\Service\OS2Forms\SubmissionManager\OS2FormsManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebformSubmissionMessageHandler implements MessageHandlerInterface
{
    public function __construct(private readonly OS2FormsManager $formsManager, private readonly HttpClientInterface $client, private $selvbetjeningUserApiToken, private readonly TokenStorageInterface $tokenStorage, private readonly AuthenticationManagerInterface $authenticationManager, private readonly UserRepository $userRepository, private readonly Security $security)
    {
    }

    public function __invoke(NewWebformSubmissionMessage $message)
    {
        // Authenticate as the OS2Forms user.
        $user = $this->userRepository->findOneBy(['name' => 'OS2Forms']);
        if (null === $user) {
            throw new WebformSubmissionException('Could not find OS2Forms system user.');
        }

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $authenticatedToken = $this->authenticationManager->authenticate($token);
        $this->tokenStorage->setToken($authenticatedToken);

        if ($user !== $this->security->getUser()) {
            throw new WebformSubmissionException('Could not authenticate as the OS2Forms user.');
        }

        $data = $message->getWebformSubmission();

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

        if (isset($data['links']['get_submission_url']) && is_string($data['links']['get_submission_url'])
            && isset($data['data']['webform']['id']) && is_string($data['data']['webform']['id'])
        ) {
            $getSubmissionUrl = $data['links']['get_submission_url'];
            $sender = $data['links']['sender'];
            $webformId = $data['data']['webform']['id'];

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

            $this->formsManager->handleOS2FormsSubmission($webformId, $sender, $submissionData);
        } else {
            $message = sprintf('Webform submission data must contain both a submission url and a webform id.');
            throw new \InvalidArgumentException($message, 400);
        }
    }
}
