
@extends('layouts.app')

@section('title', 'Tambah Jadwal Pelajaran')

@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Tambah Jadwal Pelajaran</h5>
  </div>
  
  <div class="card-body">
    <form action="{{ route('admin.jadwal-pelajaran.store') }}" method="POST">
      @csrf
      
      <div class="row mb-3">
        <label for="guru_id" class="col-sm-2 col-form-label">Guru</label>
        <div class="col-sm-10">
          <select class="form-select @error('guru_id') is-invalid @enderror" id="guru_id" name="guru_id" required>
            <option value="">-- Pilih Guru --</option>
            @foreach($guruList as $g)
              <option value="{{ $g->id }}" {{ old('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->nama_lengkap }}</option>
            @endforeach
          </select>
          @error('guru_id')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      
      <div class="row mb-3">
        <label for="kelas_id" class="col-sm-2 col-form-label">Kelas</label>
        <div class="col-sm-10">
          <select class="form-select @error('kelas_id') is-invalid @enderror" id="kelas_id" name="kelas_id" required>
            <option value="">-- Pilih Kelas --</option>
            @foreach($kelasList as $k)
              <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
            @endforeach
          </select>
          @error('kelas_id')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      
      <div class="row mb-3">
        <label for="mata_pelajaran_id" class="col-sm-2 col-form-label">Mata Pelajaran</label>
        <div class="col-sm-10">
          <select class="form-select @error('mata_pelajaran_id') is-invalid @enderror" id="mata_pelajaran_id" name="mata_pelajaran_id" required>
            <option value="">-- Pilih Mata Pelajaran --</option>
            @foreach($mataPelajaranList as $m)
              <option value="{{ $m->id }}" {{ old('mata_pelajaran_id') == $m->id ? 'selected' : '' }}>{{ $m->nama_mata_pelajaran }} </option>
            @endforeach
          </select>
          @error('mata_pelajaran_id')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Hari</label>
                                 <div class="col-sm-10">
                                <select name="hari" class="form-select @error('hari') is-invalid @enderror" required>
                                    <option value="">-- Pilih Hari --</option>
                                    @foreach($hariList as $hari)
                                        <option value="{{ $hari }}" {{ old('hari') == $hari ? 'selected' : '' }}>
                                            {{ ucfirst($hari) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('hari')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            </div>
      
                           <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Jam Ke</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control @error('jam_ke') is-invalid @enderror" 
                                       name="jam_ke" value="{{ old('jam_ke') }}" required>
                                @error('jam_ke')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>

      <div class="row mb-3">
        <label for="jam_mulai" class="col-sm-2 col-form-label">Jam Mulai</label>
        <div class="col-sm-10">
          <input type="time" class="form-control @error('jam_mulai') is-invalid @enderror" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai') }}" required>
          @error('jam_mulai')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      
      <div class="row mb-3">
        <label for="jam_selesai" class="col-sm-2 col-form-label">Jam Selesai</label>
        <div class="col-sm-10">
          <input type="time" class="form-control @error('jam_selesai') is-invalid @enderror" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai') }}" required>
          @error('jam_selesai')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

                           <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Tahun Ajaran</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control @error('tahun_ajaran') is-invalid @enderror" 
                                       name="tahun_ajaran" value="{{ old('tahun_ajaran') }}" required>
                                @error('tahun_ajaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Semester</label>
                                 <div class="col-sm-10">
                                <select name="semester" class="form-select @error('semester') is-invalid @enderror" required>
                                    <option value="">-- Pilih Semester --</option>
                                    @foreach($semesterList as $semester)
                                        <option value="{{ $semester }}" {{ old('semester') == $semester ? 'selected' : '' }}>
                                            {{ ucfirst($semester) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('semester')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            </div>
      
      
      <div class="row">
        <div class="col-sm-10 offset-sm-2">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <a href="{{ route('admin.jadwal-pelajaran.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
