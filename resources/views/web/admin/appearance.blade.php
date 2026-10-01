@extends('web.admin.layout')

@section('title', 'Appearance & Images')

@section('content')

<style>
.appearance-heading {
    margin-bottom: 24px;
}

.appearance-heading h1 {
    margin: 0 0 7px;
    color: #173e31;
    font-size: 28px;
}

.appearance-heading p {
    margin: 0;
    color: #6b7c74;
    font-size: 13px;
    line-height: 1.6;
}

.appearance-note {
    margin-bottom: 22px;
    padding: 14px 16px;

    border-left: 4px solid #006b45;
    border-radius: 7px;

    background: #edf7f2;
    color: #34584a;

    font-size: 12px;
    line-height: 1.6;
}

.appearance-grid {
    display: grid;
    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 20px;
}

.image-card {
    overflow: hidden;

    border: 1px solid #dce6e1;
    border-radius: 11px;

    background: #fff;

    box-shadow:
        0 7px 22px rgba(23,62,49,.06);
}

.image-preview {
    position: relative;

    height: 220px;

    overflow: hidden;

    background: #e9efec;
}

.image-preview img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;
    object-position: center;
}

.image-status {
    position: absolute;

    top: 13px;
    right: 13px;

    padding: 6px 9px;

    border-radius: 999px;

    background: rgba(0,75,53,.90);
    color: #fff;

    font-size: 9px;
    font-weight: 800;

    text-transform: uppercase;
}

.image-card-body {
    padding: 19px;
}

.image-card h2 {
    margin: 0 0 7px;

    color: #173e31;
    font-size: 17px;
}

.image-description {
    min-height: 38px;

    margin: 0 0 17px;

    color: #6a7b73;

    font-size: 11px;
    line-height: 1.55;
}

.upload-form {
    padding-top: 15px;

    border-top: 1px solid #edf1ef;
}

.upload-form label {
    display: block;

    margin-bottom: 7px;

    color: #344f44;

    font-size: 11px;
    font-weight: 800;
}

.upload-form input[type="file"] {
    width: 100%;

    padding: 9px;

    border: 1px solid #cedbd4;
    border-radius: 6px;

    background: #fff;

    font-size: 11px;
}

.image-actions {
    display: flex;
    gap: 9px;

    margin-top: 12px;
}

.image-actions button {
    min-height: 39px;

    padding: 0 15px;

    border-radius: 6px;

    font-size: 11px;
    font-weight: 800;

    cursor: pointer;
}

.replace-button {
    border: 1px solid #006b45;

    background: #006b45;
    color: #fff;
}

.replace-button:hover {
    background: #004b35;
}

.reset-button {
    border: 1px solid #cbd9d2;

    background: #fff;
    color: #38584a;
}

.alert-success {
    margin-bottom: 20px;

    padding: 13px 15px;

    border: 1px solid #bee0ce;
    border-radius: 7px;

    background: #eaf7f0;
    color: #17613d;

    font-size: 12px;
}

.alert-error {
    margin-bottom: 20px;

    padding: 13px 15px;

    border: 1px solid #efcaca;
    border-radius: 7px;

    background: #fff0f0;
    color: #8a2929;

    font-size: 12px;
}

@media (max-width: 850px) {
    .appearance-grid {
        grid-template-columns: 1fr;
    }
}
</style>


<div class="appearance-heading">

    <h1>
        Appearance & Images
    </h1>

    <p>
        Replace the main photographs used across SmartLog.
    </p>

</div>


<div class="appearance-note">

    <strong>Editable:</strong>
    main hero and dashboard photographs.

    <br>

    <strong>Fixed:</strong>
    SmartLog branding, navigation, icons, buttons and
    structural interface elements.

</div>


@if(session('success'))

    <div class="alert-success">
        {{ session('success') }}
    </div>

@endif


@if($errors->any())

    <div class="alert-error">
        {{ $errors->first() }}
    </div>

@endif


<div class="appearance-grid">

    @foreach($slots as $key => $slot)

        <article class="image-card">

            <div class="image-preview">

                <img
                    src="{{ $slot['url'] }}"
                    alt="{{ $slot['title'] }}"
                >

                <span class="image-status">
                    {{ $slot['custom_exists'] ? 'Custom' : 'Default' }}
                </span>

            </div>


            <div class="image-card-body">

                <h2>
                    {{ $slot['title'] }}
                </h2>

                <p class="image-description">
                    {{ $slot['description'] }}
                </p>


                <form
                    class="upload-form"
                    method="POST"
                    action="{{ route('web.admin.appearance.upload', $key) }}"
                    enctype="multipart/form-data"
                >

                    @csrf

                    <label>
                        Choose replacement picture
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        required
                    >

                    <div class="image-actions">

                        <button
                            type="submit"
                            class="replace-button"
                        >
                            Replace Image
                        </button>

                    </div>

                </form>


                @if($slot['custom_exists'])

                    <form
                        method="POST"
                        action="{{ route('web.admin.appearance.reset', $key) }}"
                        onsubmit="return confirm('Reset this picture to the SmartLog default?');"
                    >

                        @csrf
                        @method('DELETE')

                        <div class="image-actions">

                            <button
                                type="submit"
                                class="reset-button"
                            >
                                Reset to Default
                            </button>

                        </div>

                    </form>

                @endif

            </div>

        </article>

    @endforeach

</div>

@endsection