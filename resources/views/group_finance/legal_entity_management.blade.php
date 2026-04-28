@extends('layouts.admin')

@section('content')
    @php $roleNames = session('admin_role_names', []); @endphp
    @php $canEdit = in_array('Super Admin', $roleNames, true) || in_array('Entity Manager', $roleNames, true); @endphp
    @php $canDelete = in_array('Super Admin', $roleNames, true) || in_array('Entity Manager', $roleNames, true); @endphp

    <div class="legal-entity-page">
    <div class="page-head legal-head">
        <div class="legal-head-left">
            <div class="breadcrumb-dark">Dashboard <span class="sep">/</span> Legal Entity Management</div>
            <h4 class="page-title">Legal Entity Management</h4>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-end align-items-start" style="max-width: min(100%, 56rem);">
            <button type="button" class="btn legal-top-btn legal-add-btn grm-coming-soon-btn" onclick="comingSoon()">Entity Detail View</button>
            <button type="button" class="btn legal-top-btn legal-add-btn grm-coming-soon-btn" onclick="comingSoon()">Share Holders</button>
            <button type="button" class="btn legal-top-btn legal-add-btn grm-coming-soon-btn" onclick="comingSoon()">Upload Entities / Exception Report</button>
            <button type="button" class="btn legal-top-btn legal-add-btn grm-coming-soon-btn" onclick="comingSoon()">Entity Comparison</button>
            <button type="button" class="btn legal-top-btn legal-add-btn grm-coming-soon-btn" onclick="comingSoon()">Entity Logs</button>
            <button type="button" class="btn legal-top-btn legal-add-btn grm-coming-soon-btn" onclick="comingSoon()">Single User Unlock Period</button>
            <button type="button" class="btn btn-danger legal-add-btn grm-coming-soon-btn border-0" onclick="comingSoon()">Lock Period</button>
            @if($canEdit)
                <a href="{{ route('group_finance.legal_entity_management', ['year' => $year, 'period' => $period, 'create' => 1]) }}" class="btn btn-add legal-add-btn" id="openEntityCreateDrawer"><i class="bi bi-plus-circle me-1"></i>Add New Entity</a>
            @endif
        </div>
    </div>

    <div class="card fade-in-up legal-card legal-filter-shell mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('group_finance.legal_entity_management') }}" class="filter-card legal-filter-card mb-0">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Year</label>
                        @include('partials.grm-year-select', ['name' => 'year', 'selected' => $year])
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Month</label>
                        @include('partials.grm-month-select', ['name' => 'period', 'selected' => $period])
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Group Structure</label>
                        <select name="group_parent" class="form-select">
                            <option value="">All</option>
                            @foreach($groupStructureOptions as $opt)
                                <option value="{{ $opt->value }}" @if(($groupParent ?? '') !== '' && (int)$groupParent === (int)$opt->value) selected @endif>{{ $opt->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex flex-wrap gap-2 legal-filter-actions align-items-end">
                        <button type="submit" class="btn btn-add btn-filter legal-filter-btn"><i class="bi bi-funnel me-1"></i>Apply Filter</button>
                        <button type="button" class="btn btn-add btn-filter legal-filter-btn grm-toolbar-soon" onclick="comingSoon()"><i class="bi bi-diagram-3 me-1"></i>Generate Graph</button>
                        <button type="button" class="btn btn-add btn-filter legal-filter-btn grm-toolbar-soon" onclick="comingSoon()">Appointments</button>
                        <a href="{{ route('group_finance.legal_entity_management') }}" class="btn btn-ghost btn-filter legal-filter-btn"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card fade-in-up legal-card legal-table-shell mb-3">
        <div class="card-body">
            <div class="legal-table-search-block mb-2">
                <div id="legalDatatableSearchMount" class="legal-datatable-search-mount"></div>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-2 px-1">
                <button type="button" class="btn btn-add btn-filter legal-filter-btn grm-toolbar-soon" onclick="comingSoon()">Attachment Expiry</button>
                <button type="button" class="btn btn-add btn-filter legal-filter-btn grm-toolbar-soon" onclick="comingSoon()">Draft Entities</button>
                <button type="button" class="btn btn-sm btn-filter grm-toolbar-soon grm-export-purple" onclick="comingSoon()">Export Data</button>
                <button type="button" class="btn btn-sm btn-success btn-filter grm-toolbar-soon" onclick="comingSoon()">Export to Excel</button>
                <button type="button" class="btn btn-sm btn-danger btn-filter grm-toolbar-soon" onclick="comingSoon()">Export to PDF</button>
            </div>
            <div class="table-container">
                <div class="table-responsive">
                <table class="table legal-entity-table w-100" id="entityTable">
                    <thead class="table-light">
                        <tr>
                            <th>Entity #</th>
                            <th>Legal Entity Name</th>
                            <th>Company Status</th>
                            <th>Registered Office</th>
                            <th>Legal Entity Type</th>
                            <th>Trade License#</th>
                            <th>Country</th>
                            <th>Jurisdiction</th>
                            <th>Share Capital</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td class="mono">{{ $row->entity_code }}</td>
                                <td>{{ $row->entity_name }}</td>
                                <td>
                                    @php
                                        $status = (string)$row->company_status;
                                        $statusLabel = ucwords(str_replace('_', ' ', $status));
                                        $stRaw = strtolower($status);
                                        $pill = str_contains($stRaw, 'active') ? 'active' : (str_contains($stRaw, 'dormant') ? 'dormant' : (str_contains($stRaw, 'disposed') ? 'disposed' : (str_contains($stRaw, 'under_liquidation') ? 'under-liquidation' : (str_contains($stRaw, 'liquid') ? 'liquidation' : 'neutral'))));
                                    @endphp
                                    <span class="status-pill {{ $pill }}">{{ $statusLabel }}</span>
                                </td>
                                <td>{{ $row->registered_office }}</td>
                                <td>{{ $row->legal_entity_type }}</td>
                                <td>{{ $row->trade_license_number }}</td>
                                <td>{{ $row->country_name }}</td>
                                <td>{{ $row->jurisdiction }}</td>
                                <td class="text-end">{{ number_format((float)$row->share_capital, 0) }}</td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('group_finance.legal_entity_management.detail', ['mappingId' => $row->mapping_id]) }}" class="icon-action view" title="View"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('group_finance.legal_entity_management.graph', ['mappingId' => $row->mapping_id]) }}" class="icon-action graph" title="Graph"><i class="bi bi-diagram-3"></i></a>
                                        @if($canDelete)
                                            <form method="POST" action="{{ route('group_finance.legal_entity_management') }}" data-delete-form="true" data-delete-message="Delete this entity?">
                                                @csrf
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="mapping_id" value="{{ $row->mapping_id }}">
                                                <input type="hidden" name="year" value="{{ $year }}">
                                                <input type="hidden" name="period" value="{{ $period }}">
                                                <button class="icon-action delete" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endif
                                        @if($canEdit)
                                            <a
                                                href="{{ route('group_finance.legal_entity_management', ['year' => $year, 'period' => $period, 'edit_mapping_id' => $row->mapping_id]) }}"
                                                class="icon-action edit js-edit-entity"
                                                title="Edit"
                                                data-mapping-id="{{ $row->mapping_id }}"
                                                data-entity-name="{{ e($row->entity_name ?? '') }}"
                                                data-legal-entity-type="{{ e($row->legal_entity_type ?? '') }}"
                                                data-country-id="{{ $row->country_id ?? '' }}"
                                                data-jurisdiction="{{ e($row->jurisdiction ?? '') }}"
                                                data-incorporation-date="{{ $row->incorporation_date ?? '' }}"
                                                data-registered-office-address="{{ e($row->registered_office_address ?? '') }}"
                                                data-trade-license-number="{{ e($row->trade_license_number ?? '') }}"
                                                data-trade-license-expiry-date="{{ $row->trade_license_expiry_date ?? '' }}"
                                                data-share-capital="{{ $row->share_capital ?? '' }}"
                                                data-number-of-shares="{{ $row->number_of_shares ?? '' }}"
                                                data-company-status="{{ $row->company_status ?? '' }}"
                                                data-assigned-to="{{ $row->assigned_to ?? '' }}"
                                            ><i class="bi bi-pencil"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="entityFormOffcanvas" aria-labelledby="entityFormOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="entityFormOffcanvasLabel">
                @if($actionMode === 'update') Edit Entity @else Add New Entity @endif
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            @if($canEdit)
                <form method="POST" action="{{ route('group_finance.legal_entity_management') }}" id="entityUpsertForm">
                    @csrf

                    <input type="hidden" name="action" value="{{ $actionMode ?? 'create' }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="hidden" name="mapping_id" value="{{ old('mapping_id', $editRow['mapping_id'] ?? '') }}">

                    <div class="mb-3">
                        <label class="form-label">Legal Entity Name <span class="text-danger">*</span></label>
                        <input type="text" name="entity_name" class="form-control @error('entity_name') is-invalid @enderror" value="{{ old('entity_name', $editRow['entity_name'] ?? '') }}">
                        @error('entity_name')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Legal Entity Type <span class="text-danger">*</span></label>
                        <input type="text" name="legal_entity_type" class="form-control @error('legal_entity_type') is-invalid @enderror" value="{{ old('legal_entity_type', $editRow['legal_entity_type'] ?? '') }}">
                        @error('legal_entity_type')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Country of Incorporation <span class="text-danger">*</span></label>
                        <select name="country_id" class="form-select @error('country_id') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" @if((string)(old('country_id', $editRow['country_id'] ?? '')) === (string)$c->id) selected @endif>{{ $c->country_name }}</option>
                            @endforeach
                        </select>
                        @error('country_id')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jurisdiction of Incorporation</label>
                        <input type="text" name="jurisdiction" class="form-control @error('jurisdiction') is-invalid @enderror" value="{{ old('jurisdiction', $editRow['jurisdiction'] ?? '') }}">
                        @error('jurisdiction')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Incorporation Date <span class="text-danger">*</span></label>
                        <input type="date" name="incorporation_date" class="form-control @error('incorporation_date') is-invalid @enderror" value="{{ old('incorporation_date', isset($editRow['incorporation_date']) ? $editRow['incorporation_date'] : '') }}">
                        @error('incorporation_date')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Registered Office Address</label>
                        <textarea name="registered_office_address" class="form-control @error('registered_office_address') is-invalid @enderror" rows="3">{{ old('registered_office_address', $editRow['registered_office_address'] ?? '') }}</textarea>
                        @error('registered_office_address')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Trade License Number</label>
                        <input type="text" name="trade_license_number" class="form-control @error('trade_license_number') is-invalid @enderror" value="{{ old('trade_license_number', $editRow['trade_license_number'] ?? '') }}">
                        @error('trade_license_number')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Trade License Expiry Date</label>
                        <input type="date" name="trade_license_expiry_date" class="form-control @error('trade_license_expiry_date') is-invalid @enderror" value="{{ old('trade_license_expiry_date', $editRow['trade_license_expiry_date'] ?? '') }}">
                        @error('trade_license_expiry_date')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Share Capital <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="share_capital" class="form-control @error('share_capital') is-invalid @enderror" value="{{ old('share_capital', $editRow['share_capital'] ?? '') }}">
                        @error('share_capital')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Number of Shares</label>
                        <input type="number" name="number_of_shares" class="form-control @error('number_of_shares') is-invalid @enderror" value="{{ old('number_of_shares', $editRow['number_of_shares'] ?? '') }}">
                        @error('number_of_shares')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Company Status <span class="text-danger">*</span></label>
                        <select name="company_status" class="form-select @error('company_status') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach(['Active','Disposed','Under_liquidation','Dormant'] as $st)
                                <option value="{{ $st }}" @if((string)old('company_status', $editRow['company_status'] ?? '') === (string)$st) selected @endif>{{ $st }}</option>
                            @endforeach
                        </select>
                        @error('company_status')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach(['Finance','Legal'] as $a)
                                <option value="{{ $a }}" @if((string)old('assigned_to', $editRow['assigned_to'] ?? '') === (string)$a) selected @endif>{{ $a }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                    </div>
                </form>
            @else
                <div class="text-muted">You do not have access to create/edit entities.</div>
            @endif
        </div>
    </div>

    <div id="grmEntityLoadingOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.15); z-index:9999;">
        <div class="d-flex align-items-center justify-content-center h-100">
            <div class="card p-3">
                <div class="spinner-border text-success me-2" role="status" aria-hidden="true"></div>
                <span class="fw-semibold">Loading...</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const open = {{ $openOffcanvas ? 'true' : 'false' }};
            const offcanvasEl = document.getElementById('entityFormOffcanvas');
            const createBtn = document.getElementById('openEntityCreateDrawer');
            const entityForm = document.getElementById('entityUpsertForm');
            const titleEl = document.getElementById('entityFormOffcanvasLabel');
            let bsOffcanvas = null;

            if (offcanvasEl) {
                bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                if (open) bsOffcanvas.show();
            }

            if (createBtn && bsOffcanvas) {
                createBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (entityForm) {
                        const yearValue = entityForm.querySelector('input[name="year"]')?.value || '';
                        const periodValue = entityForm.querySelector('input[name="period"]')?.value || '';
                        entityForm.reset();
                        entityForm.querySelector('input[name="action"]').value = 'create';
                        entityForm.querySelector('input[name="mapping_id"]').value = '';
                        entityForm.querySelector('input[name="year"]').value = yearValue;
                        entityForm.querySelector('input[name="period"]').value = periodValue;
                        if (window.jQuery) jQuery(entityForm).find('select').trigger('change.select2');
                    }
                    if (titleEl) titleEl.textContent = 'Add New Entity';
                    bsOffcanvas.show();
                });
            }

            document.querySelectorAll('.js-edit-entity').forEach(function (editBtn) {
                editBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (!entityForm || !bsOffcanvas) return;

                    const get = function (key) { return editBtn.getAttribute('data-' + key) || ''; };
                    const set = function (name, value) {
                        const el = entityForm.querySelector('[name="' + name + '"]');
                        if (el) el.value = value ?? '';
                    };

                    set('action', 'update');
                    set('mapping_id', get('mapping-id'));
                    set('entity_name', get('entity-name'));
                    set('legal_entity_type', get('legal-entity-type'));
                    set('country_id', get('country-id'));
                    set('jurisdiction', get('jurisdiction'));
                    set('incorporation_date', get('incorporation-date'));
                    set('registered_office_address', get('registered-office-address'));
                    set('trade_license_number', get('trade-license-number'));
                    set('trade_license_expiry_date', get('trade-license-expiry-date'));
                    set('share_capital', get('share-capital'));
                    set('number_of_shares', get('number-of-shares'));
                    set('company_status', get('company-status'));
                    set('assigned_to', get('assigned-to'));

                    if (window.jQuery) jQuery(entityForm).find('select').trigger('change.select2');
                    if (titleEl) titleEl.textContent = 'Edit Entity';
                    bsOffcanvas.show();
                });
            });

            const overlay = document.getElementById('grmEntityLoadingOverlay');
            if (entityForm) {
                // Show local loading overlay only for create/update drawer submit.
                // Delete forms use GrmUI + SweetAlert flow and should not be blocked here.
                entityForm.addEventListener('submit', function () {
                    if (overlay) overlay.style.display = 'block';
                });
            }

            if (window.jQuery && $('#entityTable').length) {
                const $mount = $('#legalDatatableSearchMount');
                $('#entityTable').DataTable({
                    pageLength: 50,
                    order: [[0, 'desc']],
                    lengthChange: false,
                    pagingType: 'simple_numbers',
                    searching: true,
                    dom: "<'legal-common-search-wrap mb-0'f>t<'legal-table-footer d-flex justify-content-between align-items-center mt-2 pt-2'<'legal-footer-info'i><'legal-footer-pagination'p>>",
                    initComplete: function () {
                        const $wrap = $('#entityTable_wrapper').children('.legal-common-search-wrap').first();
                        if ($wrap.length && $mount.length) {
                            $wrap.appendTo($mount);
                        } else {
                            $('#entityTable_filter').appendTo($mount);
                        }
                        $('#entityTable_filter input[type="search"]').attr('placeholder', 'Search...');
                    }
                });
            }
        });
    </script>
    </div>
@endsection

