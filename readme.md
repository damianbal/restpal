# RestPal 😌

Consume and Manipulate your Laravel models restfully, make your API development happy!

## Getting Started

RestPal lets you to create, update, list, get your Laravels models without creating
routes for specific Models. See Example section to see how easy it is!

### Routes

* GET /resource/MODEL?page=X&sortBy=column_name&sortOrder=DESC|ASC&perPage=3 - lists all items from model
* GET /resource/{model}/{id} - lists only one item by id
* POST /resource/{model} - create new item
* PATCH /resource/{model}/{id} - update item 
* DELETE /resource/{model}/{id} - removes item 

### Policies

If model has policy (ModelPolicy in Policies directory) then it is going to be used to see if user can do something with resource, if there is no policy for that Model then anybody can do anything with that Model, so make sure to create policies for Models.

If you create policy it must have three methods:

```php
public function create(User $user)
public function update(User $user, Model $model)
public function delete(User $user, Model $model)
```

If true is returned without any condition then any signed in user can do that action.

### Resources

If model has resource (ModelResource in Resources directory) then it is going to be used to return data, if there is no Resource for it then only model is going to be returned without hidden attributes.

It is better to create resources for models when using Restpal, even just default ones.

```sh
php artisan make:resource ModelResource
```

### Validation

If you want to validate POST (create) and PATCH (update) requests then you need to use singleton 'damianbal\Restpal\RestpalConfiguration'.

You can resolve RestpalConfiguration in controllers constructor or set validations in AppServiceProvider.php

```php
$restpalConfig = $this->app->make(RestpalConfiguration::class);

$restpalConfig->setValidation('Article', 'create', [
    'title' => 'required|min:3',
    'content' => 'required|min:3'
]);
```

If there is no validation for Model and Action then resource is going to be created without any validation, so make sure that you have
validations added.

### Example 

Let's say that we have Article model with two columns which are title and content, and of course 
id, and timestamps.

We would write that to create article
```js
let article = {
    title: "This is my article!",
    content: "Hello :)"
}

axios.post('http://laravel-app.com/resource/Article', article)
```

If we would like to get article we would do that
```js
axios.get('http://laravel-app.com/resource/Article/3').then(resp => {
    console.log(resp.data) // { title: "some title", content: "..." }
})
```

To list all the articles with pagination and sorting
```js
// page, sortBy, sortOrder all of them are optional 
// default page is 1
// default sortBy is created_at
// default sortOrder is DESC
axios.get('http://larave-app.com/resource/Article&page=3&sortBy=created_at&sortOrder=DESC').then(resp => {
    console.log(resp.data) // 
})
```

To update the article
```js
let articleId = 9

let newData = {title:"New title of article 9"}

axios.patch('http://laravel-app.com/resource/Article/' + articleId, newData)
```

And of course to delete (as easy to guess) would be
```js
axios.delete('http://laravel-app.com/resource/Article/4') // delete article with id of 4
```

## Installing

```sh
composer require damianbal\restpal
```

```sh
php artisan vendor:publish
```

and then select damianbal\\Restpal\\RestpalServiceProvider

You do not need to put ServiceProvider in config/app.php, because this package supports 
auto-discovery 😉

 