<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\PostCountController;
use App\Controller\PostPublishController;
use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Valid;

#[ORM\Entity(repositoryClass: PostRepository::class)]

#[ApiResource(
    collectionOperations: [
        'get',
        'post',
        'count' => [
            'method' => 'GET',
            'path' => '/posts/count',
            'controller' => PostCountController::class,
            'filters' => [],
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Get counts of posts.',
                'parameters' => [
                    [
                        'in' => 'query',
                        'name' => 'online',
                        'schema' => [
                            'type' => 'integer',
                            'maximum' => 1,
                            'minimum' => 0
                        ],
                        'description' => 'Filter online posts'
                    ]
                ]
            ]
        ]
        /*=> [
            'validation_groups' => [
                'create:Post:item'
                // Post::class, 'validationGroups'
            ]
        ]*/
    ],
    itemOperations: [
        // EXPLICATION:
        //      Allow us to control which property is authorized to access defined operations with groups annotations
        //      and what kind of operations we can access from API endpoint on the UI.
        // ACTION:
        //      Define groups and actions.
        'delete',
        'put',
        'get' => [
            'normalization_context' => [
                'groups' => ['read:Post:collection', 'read:Post:item', 'read:Post'],
                'openapi_definition_name' => 'Detail'
            ]
        ],
        'publish' => [
            'method' => 'POST',
            'path' => '/posts/{id}/publish',
            // If we need to deactivate verifications
            // 'read' => false,
            // Delete the persistent, this mean this will not be written in DB
            // 'write' => false,
            'controller' => PostPublishController::class,
            'openapi_context' => [
                'summary' => 'Pass post status online',
                'requestBody' => [
                    'required' => false,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => []
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'OK',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'integer',
                                    'example' => 3
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    // Specify operations we only want in the ui
    denormalizationContext: ['groups' => 'write:Post:item'],
    normalizationContext: [
        'groups' => 'read:Post:collection',
        'openapi_definition_name' => 'Collection'
    ],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 2,
    paginationMaximumItemsPerPage: 2
)]

// Add specific search options for GET collection on "/api" resource.
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'title' => 'partial'])]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:Post:collection'])]
    private $id;

    #[
        Groups(['read:Post:collection', 'write:Post:item']),
        Length(min: 5, groups: ['create:Post:item'])
    ]
    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[Groups(['read:Post:collection', 'write:Post:item'])]
    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    #[Groups(['read:Post:item', 'write:Post:item'])]
    #[ORM\Column(type: 'string', length: 255)]
    private $content;

    #[Groups(['read:Post:item'])]
    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private $updateAt;

    #[
        Groups(['read:Post:item', 'read:Post', 'write:Post:item']),
        Valid()
    ]
    #[ORM\ManyToOne(targetEntity: Category::class, cascade: ['persist'], inversedBy: 'posts')]
    private $category;

    #[Groups(['read:Post:collection'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    #[ApiProperty(openapiContext: ['type' => 'boolean', 'description' => 'Status of post'])]
    private $online;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTimeImmutable());
        $this->setUpdateAt(new \DateTimeImmutable());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeImmutable $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    static function validationGroups(self $post)
    {
        return ['create:Post'];
    }

    public function getOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(?bool $online): self
    {
        $this->online = $online;

        return $this;
    }
}
