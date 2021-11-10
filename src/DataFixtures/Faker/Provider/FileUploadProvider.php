<?php

namespace App\DataFixtures\Faker\Provider;

use Faker\Generator;
use Faker\Provider\Base;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

class FileUploadProvider extends Base
{
    /**
     * @var PropertyMappingFactory
     */
    private $propertyMappingFactory;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Generator $generator, ContainerInterface $container, PropertyMappingFactory $propertyMappingFactory, Filesystem $filesystem)
    {
        parent::__construct($generator);
        $this->container = $container;
        $this->propertyMappingFactory = $propertyMappingFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Upload a file.
     *
     * @param string $path      the file path relative to the fixtures directory
     * @param string $property  the object property
     * @param string $className the object class name
     *
     * @return string the file path relative to the configured upload destination
     */
    public function uploadFile(string $path, string $property, string $className)
    {
        $mapping = $this->propertyMappingFactory->fromField([], $property, $className);
        $mapping->getUploadDestination();

        $path = ltrim($path, '/');
        $sourcePath = $this->container->getParameter('kernel.project_dir').'/fixtures/'.$path;
        $targetPath = $mapping->getUploadDestination().'/'.$path;

        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException(sprintf('File source path %s does not exist', $sourcePath));
        }
        $this->filesystem->copy($sourcePath, $targetPath);

        return $path;
    }
}
