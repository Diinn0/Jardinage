<?php

namespace App\Service;


use App\Repository\CategoryRepository ;
use Doctrine\Common\Collections\ArrayCollection;

class CategoryService {

    private $categoryRepository;

    // We need to inject these variables for later use.
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function displayAllCategories()
    {
        $categories = $this->categoryRepository->findAll();
        if ($categories == null) {
            $categories = new ArrayCollection();
        }
        return $categories;
    }
}