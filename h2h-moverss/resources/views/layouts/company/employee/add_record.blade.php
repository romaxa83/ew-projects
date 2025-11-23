<div id="add_record" class="panel"{!! isset($hide) ? ' style="display:none"':'' !!}>
    <div class="panel-hdr">
        <h2>
            New Employee
        </h2>
    </div>
    <div class="panel-container show">
        <div class="panel-content">

            <form method="post" action="{{ route('company.employees.record.create') }}">
                <div class="row">
                    @csrf
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label" for="name"><sup>*</sup>Name</label>
                            <input name="name" class="form-control @error('name') is-invalid @enderror" id="name"
                                   type="text" value="{{ old('name') }}" required>

                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label" for="l_name">Last Name</label>
                            <input name="l_name" class="form-control @error('l_name') is-invalid @enderror" id="l_name"
                                   type="text" value="{{ old('l_name') }}">

                            @error('l_name')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label" for="phone_new">Primary phone</label>
                            <input name="phone" class="form-control @error('phone') is-invalid @enderror" id="phone_new"
                                   type="text" value="{{ old('phone') }}">

                            @error('phone')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label" for="email"><sup>*</sup>Email</label>
                            <div class="input-group">
                                <input name="email" id="email" type="email" value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror" required>
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
