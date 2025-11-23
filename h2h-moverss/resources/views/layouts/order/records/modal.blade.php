<div class="modal fade" id="modal-order-create" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-fusion-200">
                <h5 class="modal-title">Create new order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <form id="order-create-form" autocomplete="off" method="post" action="">
                    <input type="hidden" name="force_create" value="0">
                    <div class="row">
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Service date</label>
                                <input type="text" id="modal-work-date" name="date" class="form-control" placeholder="Service Date"/>
                            </div>
                        </div>
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Service type</label>
                                <select class="form-control" multiple data-placeholder="Service type" id="modal-work-type">
                                    @foreach($workTypes as $workType)
                                        <option
                                            value="{{$workType->id}}"{!! $workType->id === 1 ? ' selected':'' !!}>{{$workType->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Move type</label>
                                <select name="move-type" class="form-control">
                                    @foreach(config('app.moving_types') as $k => $v)
                                        <option value="{{ $k }}">{{ $v['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Move size</label>
                                <select name="move_size_id" class="form-control">
                                    <option value="">None</option>
                                    @foreach($moveSizes as $moveSize)
                                        <option value="{{$moveSize->id}}">{{$moveSize->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Pickup Zip code</label>
                                <div class="d-flex">
                                    <div class="flex-fill">
                                        <input type="text" name="pickup[zip]" class="form-control zip-autocomplete" placeholder="Pickup Zip code"/>
                                    </div>
                                    <div class="ml-3 pt-2 custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" onclick="$('#collapseSrcAddr').collapse('toggle')" id="fill_address_src" value="1" class="custom-control-input">
                                        <label for="fill_address_src" class="custom-control-label">Fill Address</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Pickup flights of stairs</label>
                                <div class="d-flex">
                                    <div class="flex-fill">
                                        <select class="form-control" name="pickup[stairs]">
                                            <option value="">None</option>
                                            @foreach($waypointFlights as $flight)
                                                <option value="{{$flight->id}}">{{$flight->title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="ml-3 pt-2 custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" id="modal_elevator_source" name="pickup[elevator]" value="1" class="custom-control-input">
                                        <label for="modal_elevator_source" class="custom-control-label">Elevator</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collapse" id="collapseSrcAddr">
                        <div class="form-group mb-3">
                            <label class="form-label">Pickup Address</label>
                            <input type="text" name="pickup[address]" class="form-control" placeholder="Pickup Address"/>
                        </div>
                    </div>

                    <hr class="mb-2 mt-2">
                    <div class="row">
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Destination Zip code</label>
                                <div class="d-flex">
                                    <div class="flex-fill">
                                        <input type="text" name="destination[zip]" class="form-control zip-autocomplete" placeholder="Destination Zip code"/>
                                    </div>
                                    <div class="ml-3 pt-2 custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" onclick="$('#collapseDstAddr').collapse('toggle')" id="fill_address_dest" value="1" class="custom-control-input">
                                        <label for="fill_address_dest" class="custom-control-label">Fill Address</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Destination flights of stairs</label>
                                <div class="d-flex">
                                    <div class="flex-fill">
                                        <select class="form-control" name="destination[stairs]">
                                            <option value="">None</option>
                                            @foreach($waypointFlights as $flight)
                                                <option value="{{$flight->id}}">{{$flight->title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="ml-3 pt-2 custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" name="destination[elevator]" id="modal_elevator_dest" value="1" class="custom-control-input">
                                        <label for="modal_elevator_dest" class="custom-control-label">Elevator</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collapse" id="collapseDstAddr">
                        <div class="form-group mb-3">
                            <label class="form-label">Destination Address</label>
                            <input type="text" name="destination[address]" class="form-control" placeholder="Destination Address"/>
                        </div>
                    </div>

                    <h3 class="mb-3 text-info">Client info</h3>
                    <input type="hidden" name="client[id]" value=""/>
                    <div class="row">
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">First name</label>
                                <input type="text" name="client[name]" class="form-control client-autocomplete" placeholder="First name"/>
                            </div>
                        </div>
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Last name</label>
                                <input type="text" name="client[lname]" class="form-control client-autocomplete" placeholder="Last name"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" name="client[phone]" id="modal-phone" class="form-control client-autocomplete" placeholder="Phone"/>
                            </div>
                        </div>
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="text" name="client[email]" id="modal-email" class="form-control client-autocomplete" placeholder="Email"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <div class="form-group">
                                <label class="form-label">Source<sup>*</sup></label>
                                <select class="form-control" name="source" id="modal-source" date-placeholder="Not set">
                                    <option value="">Not Set</option>
                                    @foreach($sources as $source)
                                        <option value="{{$source->id}}">{{$source->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label">&nbsp;</label><br>
                            <button type="button" class="btn btn-secondary" id="modal-reset-selected" disabled>Reset selected client</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer pt-0">
                <button type="button" class="btn btn-secondary mr-auto" data-dismiss="modal">Cancel</button>
                <button type="button" name="create" class="btn btn-primary">
                    <span class="loading d-none spinner-border spinner-border-sm" role="status"
                          aria-hidden="true"></span>
                    Create order
                </button>
            </div>
        </div>
    </div>
</div>
