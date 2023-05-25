<?php

namespace DTApi\Repository;

use Validator;
use Illuminate\Database\Eloquent\Model;
use DTApi\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $validationRules = [];

    /**
     * BaseRepository constructor.
     *
     * @param Model|null $model
     */
    public function __construct(Model $model = null)
    {
        $this->model = $model;
    }

    /**
     * Get the model instance.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get all the records.
     *
     * @return \Illuminate\Database\Eloquent\Collection|Model[]
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Find a record by ID.
     *
     * @param int $id
     * @return Model|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Eager load relationships.
     *
     * @param mixed $relations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function with($relations)
    {
        return $this->model->with($relations);
    }

    /**
     * Find a record by ID or throw an exception.
     *
     * @param int $id
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Find a record by slug.
     *
     * @param string $slug
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findBySlug($slug)
    {
        return $this->model->where('slug', $slug)->firstOrFail();
    }

    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return $this->model->query();
    }

    /**
     * Create a new instance of the model.
     *
     * @param array $attributes
     * @return Model
     */
    public function instance(array $attributes = [])
    {
        return new $this->model($attributes);
    }

    /**
     * Paginate the query results.
     *
     * @param int|null $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null)
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Filter the query results by a column's value.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function where($key, $value)
    {
        return $this->model->where($key, $value);
    }

    /**
     * Get the validator instance for the given data.
     *
     * @param array $data
     * @param array|null $rules
     * @param array $messages
     * @param array $customAttributes
     * @return \Illuminate\Validation\Validator
     */
    public function validator(array $data = [], array $rules = null, array $messages = [], array $customAttributes = [])
    {
        $rules = $rules ?? $this->validationRules;

        return Validator::make($data, $rules, $messages, $customAttributes);
    }

    /**
     * Validate the given data with the specified rules.
     *
     * @param array $data
     * @param array|null $rules
     * @param array $messages
     * @param array $customAttributes
     * @return void
     * @throws ValidationException
     */
    public function validate(array $data = [], array $rules = null, array $messages = [], array $customAttributes = [])
    {
        $validator = $this->validator($data, $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Create a new record.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data = [])
    {
        return $this->model->create($data);
    }

    /**
     * Update the specified record.
     *
     * @param int $id
     * @param array $data
     * @return Model
     * @throws ModelNotFoundException
     */
    public function update($id, array $data = [])
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model;
    }

    /**
     * Delete the specified record.
     *
     * @param int $id
     * @return Model
     * @throws \Exception|ModelNotFoundException
     */
    public function delete($id)
    {
        $model = $this->findOrFail($id);
        $model->delete();
        return $model;
    }

}