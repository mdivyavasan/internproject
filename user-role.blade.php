@extends("layouts.app")

		@section("wrapper")
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Manage roles</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								</li>
								<li class="breadcrumb-item active" aria-current="page">List</li>
							</ol>
						</nav>
					</div>
				</div>
				<!--end breadcrumb-->

				<div class="card">
					<div class="card-body">
						<div class="d-lg-flex align-items-center mb-4 gap-3">
							<div class="position-relative">

							</div>
                            <div class="ms-auto">
                        <a href="javascript:void(0)"type="button" class="btn btn-primary elcredept">Create Role</a>
                            </div>
						</div>

						<div class="table-responsive">
							<table class="table mb-0">
								<thead class="table-light">
									<tr>
										<th  width="5%">S.I No</th>
										<th>Role</th>
										<th>Model Permission</th>
										<th  width="10%">Actions</th>
									</tr>
								</thead>
								<tbody>
								   <?php $i = 1; ?>
								@foreach ($rolelist as $urole)
								<tr>
                                        <td>{{ $i }}</td>
										<td>{{$urole->role_name}}
                                            <input type="hidden" id="rname_{{ $i }}" value="{{$urole->role_name}}" />
                                        </td>
									  <td><a href="{{ url('modelpermission/'.$urole->id) }}" class="btn btn-outline-primary ">Manage</a> </td>

										<td>
										    @if($urole->default_flag == 1)
												-
											@else
											<div class="d-flex order-actions">
												<a href="javascript:void(0)" dataid="{{ $i }}" class="eleeditrole"><i class='bx bxs-edit'></i></a>
												<a href="{{ url('deleterole/'.$urole->id ) }}" class="ms-3"><i class='bx bxs-trash'></i></a>
											</div>
											@endif
										</td>
									</tr>
									<?php $i++; ?>

									@endforeach


								</tbody>
							</table>
						</div>

                        <div class="col">
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form class="row g-0 needs-validation" novalidate method="post" action="{{ route('user.creatrole') }}" autocomplete="off">
                                            @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Select role</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                            <div class="col-12 p-3">
                                                <label for="role_name" class="form-label">Enter the Role Name</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control border-start-0" id="role_name" name="userrole" placeholder="role" required />
													<div class="invalid-feedback">Please Enter Your Role.</div>
                                                <div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <input type="hidden" id="roleid" name="roleid">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save </button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

					</div>
				</div>


			</div>
		</div>
		<!--end page wrapper -->
		@endsection
		@section("script")
		<script src="{{ URL::asset('assets/js/editrole.js') }}"></script>
		<script>
				// Example starter JavaScript for disabling form submissions if there are invalid fields
					(function () {
					  'use strict'

					  // Fetch all the forms we want to apply custom Bootstrap validation styles to
					  var forms = document.querySelectorAll('.needs-validation')

					  // Loop over them and prevent submission
					  Array.prototype.slice.call(forms)
						.forEach(function (form) {
						  form.addEventListener('submit', function (event) {
							if (!form.checkValidity()) {
							  event.preventDefault()
							  event.stopPropagation()
							}

							form.classList.add('was-validated')
						  }, false)
						})
					})()
			</script>
		@endsection
