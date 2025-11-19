<?php

declare(strict_types=1);

namespace Wezom\Core\ExtendPackage\Macro;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Wezom\Core\Services\Database\BlueprintWithSeo;

class BlueprintMacro
{
    public static function register(): void
    {
        self::translationTo();
        self::seo();
        self::alterEnum();
    }

    private static function translationTo(): void
    {
        /**
         * @return \Illuminate\Database\Schema\Blueprint
         */
        Blueprint::macro(
            'translationTo',
            /**
             * @template T of class-string|string
             *
             * @param  T  $translatable
             */
            function (string $translatable) {
                $table = $translatable;

                if (
                    str_contains($translatable, '\\')
                    && class_exists($translatable)
                    && is_subclass_of($translatable, Model::class)
                ) {
                    $model = new $translatable();
                    $table = $model->getTable();
                }

                /** @var Blueprint $this */
                $this->unsignedInteger('row_id');
                $this->foreign('row_id')
                    ->on($table)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $this->string('language', 3);
                $this->foreign('language')
                    ->on('languages')
                    ->references('slug')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $this->unique(['row_id', 'language']);
            }
        );
    }

    private static function seo(): void
    {
        /**
         * @return \Wezom\Core\Services\Database\BlueprintWithSeo
         */
        Blueprint::macro(
            'seo',
            function () {
                return new BlueprintWithSeo($this);
            }
        );
    }

    private static function alterEnum(): void
    {
        Blueprint::macro(
            'alterEnum',
            function (string $field, array $allowed) {
                /** @var Blueprint $this */
                $table = $this->getTable();

                $check = $this->getTable() . '_' . $field . '_check';

                $enumList = [];

                foreach ($allowed as $option) {
                    $enumList[] = sprintf("'%s'::CHARACTER VARYING", $option);
                }

                $enumString = implode(', ', $enumList);

                DB::transaction(function () use ($table, $field, $check, $enumString) {
                    DB::statement(sprintf('ALTER TABLE %s DROP CONSTRAINT %s;', $table, $check));
                    DB::statement(sprintf(
                        'ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s::TEXT = ANY (ARRAY[%s]::TEXT[]))',
                        $table,
                        $check,
                        $field,
                        $enumString
                    ));
                });
            }
        );
    }
}
