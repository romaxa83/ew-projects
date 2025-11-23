<div id="add_record" class="panel"{!! isset($hide) ? ' style="display:none"':'' !!}>
    <div class="panel-hdr">
        <h2>
            New Partners
        </h2>
    </div>
    <div class="panel-container show">
        <div class="panel-content">

            <form method="post" action="{{ route('partner.create') }}">
                <div class="row">
                    @csrf
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label" for="name"><sup>*</sup>Name</label>
                            <input name="name"
                                   class="form-control
                                   @error('name') is-invalid @enderror"
                                   id="name"
                                   type="text"
                                   value="{{ old('name') }}"
                                   required
                            >
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label" for="l_name">Contact person</label>
                            <input name="contact_person"
                                   class="form-control
                                   @error('contact_person') is-invalid @enderror"
                                   id="contact_person"
                                   type="text"
                                   value="{{ old('l_name') }}"
                            >
                            @error('contact_person')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label" for="phone_new">Phone</label>
                            <input name="phone"
                                   class="form-control
                                   @error('phone') is-invalid @enderror"
                                   id="phone_partner"
                                   type="text"
                                   value="{{ old('phone') }}"
                            >
                            @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <div class="input-group">
                                <input name="email"
                                       id="email"
                                       type="email"
                                       value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror"
                                >
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary waves-effect waves-themed" type="submit">
                                        Create
                                    </button>
                                </div>
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
