@component('mail::message')

# @lang('cms-product-reviews::admin.email.New product review')

@component('mail::table')
| @lang('cms-product-reviews::admin.email.Product') | |
| ------------ | ------------ |
| [![{{ $productReview->product->name }}]({{ $productReview->product->getImageUrl('small') }})]({{ $productReview->product->getFrontUrl() }}) | [**{{ $productReview->product->name }}**]({{ $productReview->product->getFrontUrl() }}) |
@endcomponent

@component('mail::table')
| @lang('cms-product-reviews::admin.email.Name') | @lang('cms-product-reviews::admin.email.E-mail') |
| ------------: | :------------ |
| {{ $productReview->name ?: '----' }} | [{{ $productReview->email }}](mailto:{{ $productReview->email }}) |

| @lang('cms-product-reviews::admin.email.Rating') | @lang('cms-product-reviews::admin.email.Created at') |
| ------------: | :------------ |
| {{ $productReview->rating }}/5 | {{ $productReview->created_at ? $productReview->created_at->format('d.m.Y H:i:s') : '----' }} |

| @lang('cms-product-reviews::admin.email.Text') |
| :-------------: |
| {!! str_replace(["\n", "\r"], ' ', $productReview->text) ?: '----' !!} |
@endcomponent

@component('mail::button', ['url' => $urlToAdmin])
    @lang('cms-product-reviews::admin.email.Go to admin panel')
@endcomponent

@endcomponent
