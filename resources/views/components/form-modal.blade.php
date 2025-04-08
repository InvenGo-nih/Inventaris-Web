<!-- Modal Edit Lokasi Inventaris -->
<div class="modal fade" id="modalEditLocationInventaris" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formEdit" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col mb-3">
                        <label for="editLocationInput" class="form-label">Lokasi</label>
                        <input type="text" id="editLocationInput" name="location" class="form-control" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary">Perbarui</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEdit = document.getElementById('modalEditLocationInventaris');
            const form = document.getElementById('formEdit');
            const inputLocation = document.getElementById('editLocationInput');

            modalEdit.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const location = button.getAttribute('data-location');

                form.action = `/inventaris-location/update/${id}`;
                inputLocation.value = location;
            });
        });
    </script>
@endpush

<!-- Modal Create Lokasi Inventaris -->
<div class="modal fade" id="modalCreateLocationInventaris" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('inventaris.location.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col mb-3">
                        <label for="createLocationInput" class="form-label">Lokasi</label>
                        <input type="text" id="createLocationInput" name="location" class="form-control" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary">Tambah</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Create Roles --}}
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModal" aria-hidden="true">
    <form action="{{ route('roles.store') }}" method="post">
        @csrf
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="createRoleModal">Tambah Jabatan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" placeholder="Masukkan Nama Jabatan">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
            </div>
        </div>
    </form>
</div>