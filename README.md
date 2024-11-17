# ReviewBundle

A Symfony bundle for managing book reviews. This bundle provides a complete review system for books, including:
- Review submission with ratings
- Review moderation/approval system
- Upvote and downvote system
- Configurable rating ranges
- Integration with Symfony security for user management

## Requirements

- PHP 8.2 or higher
- Symfony 7.1 or higher
- Doctrine ORM 3.3 or higher

## Installation

1. Install the bundle using Composer:
```bash
composer require rhysawd/review-bundle:@dev
```

2. Ensure the bundle has been added to your application's kernel in `config/bundles.php`:
```php
return [
    // ...
    Rhys\ReviewBundle\ReviewBundle::class => ['all' => true],
];
```

## Configuration

1. Create the review configuration file at `config/packages/review.yaml`:
```yaml
review:
    approval:
        auto_approve: false    # Set to true to auto-approve all reviews
        min_rating: 0         # Minimum allowed rating
        max_rating: 10        # Maximum allowed rating
```

2. Configure Doctrine mapping in `config/packages/doctrine.yaml`:
```yaml
doctrine:
    orm:
        mappings:
            ReviewBundle:
                type: attribute
                is_bundle: false
                dir: 'vendor/rhysawd/review-bundle/src/Entity'  
                prefix: 'Rhys\ReviewBundle\Entity'
                alias: RhysReviewBundle
```

3. Add the bundle's routes to `config/routes.yaml`:
```yaml
review_bundle:
    resource: '@ReviewBundle/Resources/config/routes.yaml'
```

4. Update your database schema:
```bash
php bin/console doctrine:schema:update --force
```

## Usage

### Adding Review Form to Book Pages

Add the review form to your book detail template:

```twig
{# templates/book/show.html.twig #}
{{ render(controller('Rhys\\ReviewBundle\\Controller\\ReviewController::new', { 'bookId': book.id })) }}
```

### Managing Reviews

The bundle provides routes for:
- Submitting new reviews: `/book/{bookId}/review/new`
- Viewing reviews: `/book/{bookId}/reviews`
- Moderating reviews (admin): `/admin/reviews`

### Customising Templates

To override the default templates, create your own versions in:
```
templates/bundles/ReviewBundle/review/
```

Available templates to override:
- `form.html.twig` - The review submission form

## Entity Relations

The bundle expects your Book entity to implement the following relationship:

```php
use Rhys\ReviewBundle\Entity\Review;
use Doctrine\Common\Collections\Collection;

class Book
{
    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'book', orphanRemoval: true)]
    private Collection $reviews;

    // ... getters and setters
}
```

And your User entity:

```php
use Rhys\ReviewBundle\Entity\Review;
use Doctrine\Common\Collections\Collection;

class User
{
    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $reviews;

    // ... getters and setters
}
```

## Events

The bundle dispatches the following events:
- `review.approved` - When a review is approved

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This bundle is released under the MIT license. See the included [LICENSE](LICENSE) file for more information.

## Support

If you find a bug or want to suggest an improvement, please create an issue on the [GitHub repository](https://github.com/itsrhys754/ReviewBundle).
```
