<?php


use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Concerns\Commentable;
use LakM\Comments\Concerns\Commenter;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\Exceptions\InvalidModelException;
use LakM\Comments\Helpers;

it('can validate commentable model', function () {
    $model1 = new class () extends Model implements CommentableContract {
        use Commentable;
    };

    $model2 = new class () extends Model {
    };

    expect(Helpers::checkCommentableModelValidity($model1))->toBeTrue()
        ->and(fn () => Helpers::checkCommentableModelValidity($model2))->toThrow(InvalidModelException::class);
});

it('can validate commenter model', function () {
    $model1 = new class () extends Illuminate\Foundation\Auth\User implements CommenterContract {
        use Commenter;
    };

    $model2 = new class () extends Illuminate\Foundation\Auth\User {
    };

    expect(Helpers::checkCommenterModelValidity($model1))->toBeTrue()
        ->and(fn () => Helpers::checkCommenterModelValidity($model2))->toThrow(InvalidModelException::class);
});
