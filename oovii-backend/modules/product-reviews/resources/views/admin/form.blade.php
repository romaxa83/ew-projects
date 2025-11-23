<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('name', __('cms-product-reviews::admin.Name')) !!}
                            {!! Form::text('name') !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('email', __('cms-product-reviews::admin.E-mail')) !!}
                            {!! Form::text('email') !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('text', __('cms-product-reviews::admin.Text')) !!}
                    {!! Form::textarea('text') !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('published', __('cms-core::admin.layout.Published')) !!}
                            {!! Form::status('published') !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('already_bought', __('cms-product-reviews::admin.Already bought')) !!}
                            {!! Form::status('already_bought', null, false) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('admin_answer', __('cms-product-reviews::admin.Admin answer')) !!}
                            {!! Form::status('admin_answer', null, false) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('like', __('cms-product-reviews::admin.Like')) !!}
                            {!! Form::status('like', null, false) !!}
                        </div>
                    </div>
                </div>

{{--                <div class="row">--}}
{{--                    <div class="col-md-4">--}}
{{--                        <div class="form-group">--}}
{{--                            {!! Form::label('rating', __('cms-product-reviews::admin.Rating')) !!}--}}
{{--                            <datalist id="rating-list" style="display: flex; justify-content: space-around">--}}
{{--                                @for($i = 1; $i < 6; $i++)--}}
{{--                                    <option value="{{ $i }}" label="{{ $i }}">--}}
{{--                                @endfor--}}
{{--                            </datalist>--}}
{{--                            {!! Form::range('rating', $obj->rating, ['min' => 1, 'max' => 5, 'step' => 1, 'list' => 'rating-list']) !!}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-6">--}}
{{--                        <div class="form-group">--}}
{{--                            {!! Form::label('likes', __('cms-product-reviews::admin.Likes')) !!}--}}
{{--                            {!! Form::number('likes', null, ['min' => 0]) !!}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-6">--}}
{{--                        <div class="form-group">--}}
{{--                            {!! Form::label('dislikes', __('cms-product-reviews::admin.Dislikes')) !!}--}}
{{--                            {!! Form::number('dislikes', null, ['min' => 0]) !!}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('created_at', __('cms-product-reviews::admin.Created at')) !!}
                            {!! Form::text('created_at',
                                old('created_at', $obj->created_at ? $obj->created_at->format('d.m.Y') : now()->format('d.m.Y')),
                                [
                                    'class' => 'js-datepicker',
                                    'placeholder' => __('cms-product-reviews::admin.Created at'),
                                    'data-end-date' => now()->format('d.m.Y'),
                                ]
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('parent_id', __('cms-product-reviews::admin.Parent review')) !!}
                            {!! Form::multiLevelSelect('parent_id', $reviews, $obj->parent_id, false, ['class' => 'form-control js-select2', 'id' => 'parent_id', 'placeholder' => __('cms-core::admin.layout.Not set')]) !!}
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('product_id', __('cms-product-reviews::admin.Product')) !!}
                    @include('cms-catalog::admin.products.select', ['products' => $products])
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            var reviewId = @json($obj->id);

            $('#product_id').on('change', function () {
                var $parentSelect = $('#parent_id');
                var $this = $(this);
                var productId = $this.val();

                if (!productId) {
                    $parentSelect.find('option').not('[value=""]').remove();
                    return;
                }

                var params = {id: productId};

                if (reviewId !== null) {
                    params.exclude = reviewId;
                }

                axios.get(route('admin.reviews-by-product-id', params))
                    .then(function (response) {
                        $parentSelect.replaceWith(response.data.select);
                        inits.select2($parentSelect);
                    });
            });
        });
    </script>
@endpush
