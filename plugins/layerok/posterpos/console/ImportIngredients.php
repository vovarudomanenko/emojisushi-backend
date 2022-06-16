<?php
namespace Layerok\PosterPos\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Layerok\PosterPos\Classes\IngredientsGroup;
use Layerok\PosterPos\Classes\PosterTransition;
use Layerok\PosterPos\Classes\RootCategory;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\Noop;
use OFFLINE\Mall\Classes\Index\ProductEntry;
use OFFLINE\Mall\Classes\Index\VariantEntry;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\ImageSet;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ProductPrice;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyGroup;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Variant;
use poster\src\PosterApi;
use Symfony\Component\Console\Input\InputOption;
use DB;

class ImportIngredients extends Command {
    protected $name = 'poster:import-ingredients';
    protected $description = 'Fetch ingredients from PosterPos api and import into database';
    protected $group = null;

    public function handle()
    {
        $question = 'All existing OFFLINE.Mall properties will be erased. Do you want to continue?';
        if ( ! $this->option('force') && ! $this->output->confirm($question, false)) {
            return 0;
        }

        // Use a Noop-Indexer so no unnecessary queries are run during seeding.
        // the index will be re-built once everything is done.
        $originalIndex = app(Index::class);
        app()->bind(Index::class, function () {
            return new Noop();
        });


        $this->cleanup();
        $this->create();


        app()->bind(Index::class, function () use ($originalIndex) {
            return $originalIndex;
        });

        if($this->option('reindex')) {
            Artisan::call('mall:reindex', ['--force' => true]);
        }

        $this->output->success('All done!');
    }

    protected function getArguments()
    {
        return [];
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Don\'t ask before deleting the data.', null],
            ['reindex', null, InputOption::VALUE_NONE, 'Reindex after importing ingredients', null],
        ];
    }

    protected function cleanup()
    {
        $this->output->writeln('Removing existing ingredients and more...');



        $group = PropertyGroup::where('name', IngredientsGroup::SLUG_KEY)->first();
        if($group) {
            $properties_ids = $group->properties->pluck('id');

            $this->output->writeln('deleting property group for ingredients');
            DB::table('offline_mall_property_groups')
                ->where('id', $group->id)
                ->delete(); // delete property group


            $this->output->writeln('deleting properties in property group for ingredients');
            DB::table('offline_mall_properties')
                ->whereIn('id', $properties_ids)
                ->delete(); // delete properties


            $this->output->writeln('deleting property values for property in property group for ingredients');
            DB::table('offline_mall_property_values')
                ->whereIn('property_id', $properties_ids)
                ->delete(); // delete property values

            $this->output->writeln('detaching property group from properties and categories');

            DB::table('offline_mall_property_property_group')
                ->where('property_group_id', $group->id)
                ->delete(); // detach properties from property group

            DB::table('offline_mall_category_property_group')
                ->where('property_group_id', $group->id)
                ->delete(); // detach categories from property group
        }

        if($this->option('reindex')) {
            $index = app(Index::class);
            $index->drop(ProductEntry::INDEX);
            $index->drop(VariantEntry::INDEX);
        }
    }

    protected function create() {
        $this->output->newLine();
        $this->output->writeln('Creating ingredients...');
        $this->output->newLine();

        $this->group = PropertyGroup::create([
            'name' => IngredientsGroup::SLUG_KEY,
            'display_name' => IngredientsGroup::DISPLAY_NAME
        ]);


        PosterApi::init();

        $this->createIngredientCategories();


    }

    protected function createIngredientCategories() {
        $this->output->newLine();
        $this->output->newLine();
        $this->output->writeln('Creating ingredient categories');
        $ingredients = (object)PosterApi::menu()->getIngredients();
        $categories = (object)PosterApi::menu()->getCategoriesIngredients();

        $count = count($categories->response);

        $this->output->progressStart($count);

        foreach ($categories->response as $category) {
            $category_id = $category->category_id;

            $options = [];

            foreach($ingredients->response as $index => $ingredient) {
                if((int)$category_id === $ingredient->category_id) {
                    $options[] = [
                        "value" => $ingredient->ingredient_name,
                        "poster_id" => $ingredient->ingredient_id,
                        "_index" => $index
                    ];
                }
            }

            $property = Property::create([
                'type' => 'dropdown',
                'poster_id' => $category_id,
                'name' => $category->name,
                'options' => $options
            ]);
            $this->group->properties()->attach($property->id, [
                'filter_type' => 'set',
                'use_for_variants' => true
            ]);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }







}
