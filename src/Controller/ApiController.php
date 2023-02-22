<?php

namespace App\Controller;

use App\Exception\ApiException;
use App\Message\NewWebformSubmissionMessage;
use App\Repository\BoardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private readonly array $serviceOptions;

    public function __construct(private readonly MessageBusInterface $bus, private readonly BoardRepository $boardRepository, array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->serviceOptions = $resolver->resolve($options);
    }

    /**
     * @throws ApiException
     */
    #[Route(path: '/api/os2forms/submission', name: 'api_add_submission_to_queue', methods: ['POST'])]
    public function addSubmissionToQueue(Request $request): Response
    {
        try {
            $this->authenticate($request);

            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $this->bus->dispatch(new NewWebformSubmissionMessage($data));

            return new Response('OK');
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        }
    }

    #[Route(path: '/api/complaint-categories/{board_id}', name: 'api_get_complaint_categories', methods: ['GET'])]
    public function getComplaintCategoriesForSelect(string $board_id, Request $request): Response
    {
        try {
            $this->authenticate($request);

            // Find board
            $board = $this->boardRepository->findOneBy(['id' => $board_id]);

            if (!$board) {
                $message = sprintf('Could not find a board with id %s', $board_id);
                throw new \InvalidArgumentException($message, 401);
            }

            $complaintCategories = $board->getComplaintCategories();

            // Create array with complaint category names as key and value
            $response = [];

            foreach ($complaintCategories as $category) {
                $response[] = ['key' => $category->getName(), 'value' => $category->getName()];
            }

            return new JsonResponse($response);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        }
    }

    private function authenticate(Request $request)
    {
        // Ensure token is correct
        // @see https://github.com/itk-dev/os2forms_selvbetjening/tree/develop/web/modules/custom/os2forms_api_request_handler#request-body
        $apiToken = $request->headers->get('authorization');

        $apiToken = preg_replace('/^Token /', '', $apiToken);

        if ($this->serviceOptions['tvist1_api_token'] !== $apiToken) {
            $message = sprintf('API token %s is invalid.', $apiToken);

            throw new \InvalidArgumentException($message, 401);
        }
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('tvist1_api_token')
        ;
    }
}
