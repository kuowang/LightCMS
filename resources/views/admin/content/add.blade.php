@extends('admin.base')
@section('css')
    <link rel="stylesheet" type="text/css" href="/public/vendor/zoom/zoom.css">
@endsection
@section('content')
    <style>
        .layui-form-item {
            margin-bottom: 5px;
        }
    </style>
    <script type="text/javascript" src="/public/vendor/neditor/third-party/jquery-1.10.2.min.js"></script>
    <script>
        function addLoadEvent(func) {
            var oldonload = window.onload;
            if (typeof window.onload != 'function') {
                window.onload = func;
            } else {
                window.onload = function () {
                    oldonload();
                    func();
                }
            }
        }
    </script>
    <div class="layui-card">

        @include('admin.breadcrumb')
        @php
            $current = 0;
            $before = 0;
            $inlineFields = [];
        @endphp
        <div class="layui-card-body">
            <form class="layui-form" action="@if(isset($id)){{ route('admin::content.update', ['id' => $id, 'entity' => $entity]) }}@else{{ route('admin::content.save', ['entity' => $entity]) }}@endif" method="post">
                @if(isset($id)) {{ method_field('PUT') }} @endif
                    @foreach($entityFields as $field)
                        @if($field->is_show == \App\Model\Admin\EntityField::SHOW_DISABLE)
                            @continue
                        @endif
                        @if(in_array($field->form_type, ['input', 'select', 'reference_category', 'selectMulti', 'selectSingleSearch', 'selectMultiSearch', 'inputAutoComplete'], true) && $field->is_show_inline == \App\Model\Admin\EntityField::SHOW_INLINE)
                            @php
                                $before = $current;
                                $current = 1;
                                array_push($inlineFields, $field);
                            @endphp
                            @continue(!$loop->last)
                        @else
                            @php
                                $before = $current;
                                $current = 0;
                            @endphp
                        @endif

                        @if($current === 0 && $before === 1 || $current === 1 && $loop->last)
                            @foreach(array_chunk($inlineFields, config('light_config.FORM_INLINE_NUM', 4)) as $inlineChunkFields)
                            <div class="layui-form-item">
                                @foreach($inlineChunkFields as $inlineField)
                                    @switch($inlineField->form_type)
                                        @case('input')
                                            <div class="layui-inline">
                                                <label class="layui-form-label">{{ $inlineField->form_name }}</label>
                                                <div class="layui-input-inline">
                                                    <input type="text" name="{{ $inlineField->name }}" @if($inlineField->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif autocomplete="off" class="layui-input" value="{{ $model->{$inlineField->name} ?? $inlineField->form_default_value  }}" @if(isset($model) && $inlineField->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                                </div>
                                            </div>
                                            @break
                                        @case('inputAutoComplete')
                                            @if(!isset($input_autocomplete_init))
                                                @php
                                                    // https://www.devbridge.com/sourcery/components/jquery-autocomplete/
                                                    $input_autocomplete_init = true
                                                @endphp
                                                <style>
                                                    .autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto; }
                                                    .autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
                                                    .autocomplete-selected { background: #F0F0F0; }
                                                    .autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
                                                    .autocomplete-group { padding: 2px 5px; }
                                                    .autocomplete-group strong { display: block; border-bottom: 1px solid #000; }
                                                </style>
                                                <!--<script type="text/javascript" src="/public/vendor/neditor/third-party/jquery-1.10.2.min.js"></script>-->
                                                <script type="text/javascript" src="https://cdn.staticfile.org/jquery.devbridge-autocomplete/1.4.11/jquery.autocomplete.min.js"></script>
                                            @endif
                                            <div class="layui-inline">
                                                <label class="layui-form-label">{{ $inlineField->form_name }}</label>
                                                <div class="layui-input-inline">
                                                    <input type="text" name="{{ $inlineField->name }}" @if($inlineField->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif autocomplete="off" class="layui-input" value="{{ $model->{$inlineField->name} ?? $inlineField->form_default_value  }}" @if(isset($model) && $inlineField->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                                </div>
                                            </div>
                                            <script>
                                                $('input[name={{ $inlineField->name }}]').autocomplete({
                                                    dataType: 'json',
                                                    deferRequestBy: 700,
                                                    serviceUrl: '{{$inlineField->form_params}}',
                                                });
                                            </script>
                                        @break
                                        @case('select')
                                            <div class="layui-inline">
                                                <label class="layui-form-label">{{ $inlineField->form_name }}</label>
                                                <div class="layui-input-inline" style="z-index: {{99999 - ($inlineField->order + $inlineField->id)}}">
                                                    <select name="{{ $inlineField->name }}" @if($inlineField->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif @if(isset($model) && $inlineField->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                                        @foreach(parseEntityFieldParams($inlineField->form_params) as $v)
                                                            <option value="{{ $v[0] }}" @if((isset($model) && $v[0] == $model->{$inlineField->name}) || (!isset($model) && $v[0] == $inlineField->form_default_value)) selected @endif>{{ $v[1] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            @break

                                        @case('selectSingleSearch')
                                        @include('admin.formModule.selectSearch', ['selectMultiItem' => false])
                                        @break

                                        @case('selectMultiSearch')
                                        @include('admin.formModule.selectSearch', ['selectMultiItem' => true])
                                        @break

                                        @case('selectMulti')
                                            @if(!isset($selects_init))
                                                @php
                                                    // select??????????????????????????? https://github.com/hnzzmsf/layui-formSelects
                                                    $selects_init = true
                                                @endphp
                                                <link rel="stylesheet" type="text/css" href="/public/vendor/layui-v2.4.5/plugins/formSelects-v4.css"/>
                                                <!--<script type="text/javascript" src="/public/vendor/neditor/third-party/jquery-1.10.2.min.js"></script>-->
                                                <script type="text/javascript" src="/public/vendor/layui-v2.4.5/plugins/formSelects-v4.min.js"></script>
                                            @endif
                                            <div class="layui-inline">
                                                <label class="layui-form-label">{{ $inlineField->form_name }}</label>
                                                <div class="layui-input-inline" style="width: 380px;z-index: {{99999 - ($field->order + $field->id)}}">
                                                    <select xm-select-search xm-select="select-{{ $inlineField->name }}" name="{{ $inlineField->name }}" @if($inlineField->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif @if(isset($model) && $inlineField->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                                        @foreach(parseEntityFieldParams($inlineField->form_params) as $v)
                                                            @if ($inlineField->type == 'unsignedInteger')
                                                                <option value="{{ $v[0] }}" @if((isset($model) && isCheckedByAnd($v[0], $model->{$inlineField->name})) || (!isset($model) && isCheckedByAnd($v[0], $inlineField->form_default_value))) selected @endif>{{ $v[1] }}</option>
                                                            @else
                                                                <option value="{{ $v[0] }}" @if((isset($model) && isChecked($v[0], $model->{$inlineField->name})) || (!isset($model) && isChecked($v[0], $inlineField->form_default_value))) selected @endif>{{ $v[1] }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <script>
                                                formSelects.render('select-{{ $inlineField->name }}');
                                            </script>
                                            @break
                                        @case('reference_category')
                                            <div class="layui-inline">
                                                <label class="layui-form-label">{{ $inlineField->form_name }}</label>
                                                <div class="layui-input-inline" style="z-index: {{99999 - ($inlineField->order + $inlineField->id)}}">
                                                    <select name="{{ $inlineField->name }}" @if($inlineField->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif @if(isset($model) && $inlineField->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                                        @foreach(App\Repository\Admin\CategoryRepository::tree($entityModel->id) as $v)
                                                            @include('admin.category', [$v, 'fieldName' => $inlineField->name])
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            @break
                                    @endswitch
                                @endforeach
                            </div>
                            @endforeach
                            @php
                                $inlineFields = [];
                            @endphp
                            @continue($current === 1 && $loop->last)
                        @endif

                        @switch($field->form_type)
                            @case('input')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="{{ $field->name }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif autocomplete="off" class="layui-input" value="{{ $model->{$field->name} ?? $field->form_default_value  }}" @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                    </div>
                                </div>
                                @break
                            @case('inputAutoComplete')
                                @if(!isset($input_autocomplete_init))
                                    @php
                                        // https://www.devbridge.com/sourcery/components/jquery-autocomplete/
                                        $input_autocomplete_init = true
                                    @endphp
                                    <style>
                                        .autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto; }
                                        .autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
                                        .autocomplete-selected { background: #F0F0F0; }
                                        .autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
                                        .autocomplete-group { padding: 2px 5px; }
                                        .autocomplete-group strong { display: block; border-bottom: 1px solid #000; }
                                    </style>
                                    <!--<script type="text/javascript" src="/public/vendor/neditor/third-party/jquery-1.10.2.min.js"></script>-->
                                    <script type="text/javascript" src="https://cdn.staticfile.org/jquery.devbridge-autocomplete/1.4.11/jquery.autocomplete.min.js"></script>
                                @endif
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="{{ $field->name }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif autocomplete="off" class="layui-input" value="{{ $model->{$field->name} ?? $field->form_default_value  }}" @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                    </div>
                                </div>
                                <script>
                                    $('input[name={{ $field->name }}]').autocomplete({
                                        dataType: 'json',
                                        deferRequestBy: 700,
                                        serviceUrl: '{{$field->form_params}}',
                                    });
                                </script>
                                @break
                            @case('textArea')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        <textarea name="{{ $field->name }}" placeholder="???????????????" class="layui-textarea" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>{{ $model->{$field->name} ?? $field->form_default_value  }}</textarea>
                                    </div>
                                </div>
                                @break
                                @case('grade')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        <div id="grade-container-{{ $field->name }}"></div>
                                        <input type="hidden" id="grade-input-{{ $field->name }}" name="{{ $field->name }}" value="{{ $model->{$field->name} ?? $field->form_default_value }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                        <script>
                                            addLoadEvent(function () {
                                                layui.use(['rate'], function() {
                                                    var rate = layui.rate;
                                                    rate.render({
                                                        elem: '#grade-container-{{ $field->name }}'
                                                        ,length: {{ intval($field->form_params) > 0 ? intval($field->form_params) : 5 }}
                                                        ,value: {{ $model->{$field->name} ?? $field->form_default_value }} //?????????
                                                        ,text: true //????????????
                                                        ,choose: function(value){
                                                            $('#grade-input-{{ $field->name }}').val(value);
                                                        }
                                                        @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) ,readonly: true, @endif
                                                    });
                                                });
                                            });
                                        </script>
                                    </div>
                                </div>
                                @break
                            @case('markdown')
                                @if(!isset($markdown_init))
                                    @php
                                        $markdown_init = true
                                    @endphp
                                    <link rel="stylesheet" href="/public/vendor/font-awesome-4.7.0/css/font-awesome.min.css">
                                    <link rel="stylesheet" href="/public/vendor/simplemde/simplemde.min.css">
                                    <script type="text/javascript" charset="utf-8" src="/public/vendor/simplemde/simplemde.min.js"> </script>
                                    <script type="text/javascript" charset="utf-8" src="/public/vendor/inline-attachment/inline-attachment.min.js"> </script>
                                    <script type="text/javascript" charset="utf-8" src="/public/vendor/inline-attachment/codemirror-4.inline-attachment.min.js"> </script>
                                @endif
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        <textarea name="{{ $field->name }}" id="simplemde-{{ $field->name }}" placeholder="????????? Markdown ???????????????????????????????????????????????????~" ></textarea>
                                    </div></div>
                                <script>
                                    var simplemde_{{ $field->name }} = new SimpleMDE({
                                        autoDownloadFontAwesome: false,
                                        element: document.getElementById("simplemde-{{ $field->name }}"),
                                        spellChecker: false,
                                        hideIcons: ['side-by-side', 'fullscreen'],
                                        forceSync: true,
                                        autosave: {
                                            enabled: true,
                                            uniqueId: "simplemde-{{ $field->name }}",
                                            delay: 1000,
                                        },
                                    });
                                    simplemde_{{ $field->name }}.value(`{!! $model->{$field->name} ?? $field->form_default_value !!}`);
                                    var inlineAttachmentConfig = {
                                        uploadUrl: "{{ route('admin::neditor.serve', ['type' => 'uploadimage']) }}",//????????????????????????
                                        uploadFieldName: 'file',          //??????????????????
                                        jsonFieldName: 'url',              //????????????????????????????????????????????????
                                        progressText: '![???????????????...]()',    //????????????????????????????????????
                                        errorText: '??????????????????',
                                        urlText:'![????????????]({filename})',    //?????????????????????????????????????????????{filename} ???????????????????????????
                                    };
                                    inlineAttachment.editors.codemirror4.attach(simplemde_{{ $field->name }}.codemirror, inlineAttachmentConfig);
                                </script>
                                @break
                            @case('richText')
                                @if(!isset($neditor_init))
                                    @php
                                        $neditor_init = true
                                    @endphp
                                    @if(config('light_config.RICH_TEXT_EDITOR', 'neditor') === 'neditor')
                                        <script type="text/javascript" charset="utf-8" src="/public/vendor/neditor/neditor.config.js"></script>
                                        <script type="text/javascript" charset="utf-8" src="/public/vendor/neditor/neditor.all.min.js"> </script>
                                        <script type="text/javascript" charset="utf-8" src="/public/vendor/neditor/neditor.service.js"></script>
                                        <script type="text/javascript" charset="utf-8" src="/public/vendor/neditor/i18n/zh-cn/zh-cn.js"></script>
                                        <script type="text/javascript" src="/public/vendor/neditor/third-party/browser-md5-file.min.js"></script>
                                        <!--<script type="text/javascript" src="/public/vendor/neditor/third-party/jquery-1.10.2.min.js"></script>-->
                                    @else
                                        <script type="text/javascript" charset="utf-8" src="/public/vendor/ueditor/ueditor.config.js"></script>
                                        <script type="text/javascript" charset="utf-8" src="/public/vendor/ueditor/ueditor.all.min.js"> </script>
                                        <script type="text/javascript" charset="utf-8" src="/public/vendor/ueditor/lang/zh-cn/zh-cn.js"></script>
                                    @endif
                                @endif
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                    <script name="{{ $field->name }}" id="editor-{{ $field->name }}" type="text/plain" style="height:600px;">{!! $model->{$field->name} ?? $field->form_default_value !!}</script>
                                    </div></div>
                                <script>
                                    //??????????????????
                                    //????????????????????????getEditor??????????????????????????????????????????????????????????????????????????????????????????UE.getEditor('editor')???????????????????????????
                                    var ue_{{ $field->name }} = UE.getEditor('editor-{{ $field->name }}', {autoFloatEnabled:false});
                                    ue_{{ $field->name }}.ready(function(){
                                        ue_{{ $field->name }}.focus();
                                        @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE)
                                        ue_{{ $field->name }}.setDisabled();
                                        @endif
                                    });
                                </script>
                                @break
                            @case('password')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        <input type="password" name="{{ $field->name }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif autocomplete="off" class="layui-input" value="{{ $model->{$field->name} ?? $field->form_default_value  }}" @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                    </div>
                                </div>
                                @break
                            @case('uploadVideo')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        <button type="button" class="layui-btn" id="file-upload-{{ $field->name }}" @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled style="background-color: gray" @endif>
                                            <i class="layui-icon">&#xe67c;</i>????????????
                                        </button>
                                        <script type="text/javascript">
                                            addLoadEvent(function () {
                                                layui.use('upload', function(){
                                                    var upload = layui.upload;

                                                    //????????????
                                                    var uploadInst = upload.render({
                                                        accept: "video",
                                                        elem: '#file-upload-{{ $field->name }}' //????????????
                                                        ,url: "{{ route('admin::neditor.serve', ['type' => 'uploadvideo']) }}" //????????????
                                                        ,before: function(obj) {
                                                            layer.load()
                                                        }
                                                        ,done: function(res){
                                                            layer.closeAll('loading');
                                                            if (res.code != 200) {
                                                                layer.msg(res.msg)
                                                                return;
                                                            }
                                                            $('input[name={{ $field->name }}]').val(res.url);
                                                            $('#video-'+'{{ $field->name }}').attr('src', res.url);
                                                        }
                                                        ,error: function(){
                                                            layer.closeAll('loading');
                                                            layer.msg('????????????')
                                                        }
                                                    });
                                                });
                                            });
                                        </script>
                                        <div style="float: left;width: 50%">
                                        <input type="input" name="{{ $field->name }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif autocomplete="off" class="layui-input" value="{{ $model->{$field->name} ?? $field->form_default_value  }}" @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif></div>
                                        <div>
                                            <video style="max-width: 200px;height: auto" src="{{ $model->{$field->name} ?? $field->form_default_value  }}" controls="controls" id="video-{{ $field->name }}"></video>
                                        </div>
                                    </div>
                                </div>
                                @break
                            @case('uploadAttachment')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        <button type="button" class="layui-btn" id="file-upload-{{ $field->name }}" @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled style="background-color: gray" @endif>
                                            <i class="layui-icon">&#xe67c;</i>????????????
                                        </button>
                                        <script type="text/javascript">
                                            addLoadEvent(function () {
                                                layui.use('upload', function(){
                                                    var upload = layui.upload;

                                                    //????????????
                                                    var uploadInst = upload.render({
                                                        accept: "file",
                                                        elem: '#file-upload-{{ $field->name }}' //????????????
                                                        ,url: "{{ route('admin::neditor.serve', ['type' => 'uploadfile']) }}" //????????????
                                                        ,before: function(obj) {
                                                            layer.load()
                                                        }
                                                        ,done: function(res){
                                                            layer.closeAll('loading');
                                                            if (res.code != 200) {
                                                                layer.msg(res.msg)
                                                                return;
                                                            }
                                                            $('input[name={{ $field->name }}]').val(res.url);
                                                        }
                                                        ,error: function(){
                                                            layer.closeAll('loading');
                                                            layer.msg('????????????')
                                                        }
                                                    });
                                                });
                                            });
                                        </script>
                                        <div style="float: left;width: 50%">
                                        <input type="input" name="{{ $field->name }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif autocomplete="off" class="layui-input" value="{{ $model->{$field->name} ?? $field->form_default_value  }}" @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif></div>
                                    </div>
                                </div>
                                @break
                            @case('upload')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        <button type="button" class="layui-btn" id="file-upload-{{ $field->name }}" @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled style="background-color: gray" @endif>
                                            <i class="layui-icon">&#xe67c;</i>????????????
                                        </button>
                                        <script type="text/javascript">
                                            addLoadEvent(function () {
                                                layui.use('upload', function(){
                                                    var upload = layui.upload;

                                                    //????????????
                                                    var uploadInst = upload.render({
                                                        elem: '#file-upload-{{ $field->name }}' //????????????
                                                        ,url: "{{ route('admin::neditor.serve', ['type' => 'uploadimage']) }}" //????????????
                                                        ,done: function(res){
                                                            if (res.code != 200) {
                                                                layer.msg(res.msg)
                                                                return;
                                                            }
                                                            $('input[name={{ $field->name }}]').val(res.url);
                                                            $('#img-'+'{{ $field->name }}').attr('src', res.url);
                                                        }
                                                        ,error: function(){
                                                            layer.msg('????????????')
                                                        }
                                                    });
                                                });
                                            });
                                        </script>
                                        <div style="float: left;width: 50%">
                                        <input type="input" name="{{ $field->name }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif autocomplete="off" class="layui-input" value="{{ $model->{$field->name} ?? $field->form_default_value  }}" @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif></div>
                                        <div><img data-action="zoom" style="max-width: 200px;height: auto" src="{{ $model->{$field->name} ?? $field->form_default_value  }}" id="img-{{ $field->name }}"></div>
                                    </div>
                                </div>
                                @break
                                @case('uploadMulti')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        <button type="button" class="layui-btn" id="file-upload-{{ $field->name }}" @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled style="background-color: gray" @endif>
                                            <i class="layui-icon">&#xe67c;</i>????????????
                                        </button>
                                        <script type="text/javascript">

                                            addLoadEvent(function () {
                                                layui.use('upload', function(){
                                                    var upload = layui.upload;

                                                    //????????????
                                                    var uploadInst = upload.render({
                                                        elem: '#file-upload-{{ $field->name }}' //????????????
                                                        ,multiple: true
                                                        ,url: "{{ route('admin::neditor.serve', ['type' => 'uploadimage']) }}" //????????????
                                                        ,done: function(res){
                                                            if (res.code != 200) {
                                                                layer.msg(res.msg);
                                                                return;
                                                            }
                                                            var obj = $('input[name={{ $field->name }}]');
                                                            if (obj.val() === '') {
                                                                obj.val(res.url);
                                                            } else {
                                                                obj.val(obj.val() + ',' + res.url);
                                                            }

                                                            var html = '<div style="float:left"><img data-action="zoom" style="max-width: 200px;height: auto;" src="' + res.url + '" class="preview-image-{{ $field->name }}"><i title="????????????" class="layui-icon remove-image" style="font-size:20px;color:red;cursor:pointer;">&#xe640;</i>';
                                                            $('#preview-image-{{ $field->name }}').append(html);

                                                            $('i.remove-image').unbind('click').on('click', function () {
                                                                $(this).parent().remove();
                                                                var previewArr = [];
                                                                $('#preview-image-{{ $field->name }} img').each(function (i, v) {
                                                                    previewArr.push($(v).attr('src'));
                                                                });
                                                                obj.val(previewArr.join(','));
                                                            });
                                                        }
                                                        ,error: function(){
                                                            layer.msg('????????????')
                                                        }
                                                    });
                                                });
                                            });
                                        </script>
                                        <div style="float: left;width: 50%">
                                            <input type="input" name="{{ $field->name }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif autocomplete="off" class="layui-input" value="{{ $model->{$field->name} ?? ''  }}" @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif></div>
                                        <div id="preview-image-{{ $field->name }}">
                                            @if(isset($model))
                                                    @foreach(explode(',', $model->{$field->name}) as $v)
                                                        @if($v)
                                                            <div style="float:left"><img data-action="zoom" style="max-width: 200px;height: auto;" src="{{ $v }}" class="preview-image-{{ $field->name }}"><i title="????????????" class="layui-icon remove-image" style="font-size:20px;color:red;cursor:pointer;">&#xe640;</i></div>
                                                        @endif
                                                    @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @break
                            @case('reference_category')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block" style="width: 400px;z-index: {{99999 - ($field->order + $field->id)}}">
                                        <select name="{{ $field->name }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                            @foreach(App\Repository\Admin\CategoryRepository::tree($entityModel->id) as $v)
                                                @include('admin.category', [$v, 'fieldName' => $field->name])
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @break
                            @case('reference_admin_user')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block" style="width: 400px;z-index: {{99999 - ($field->order + $field->id)}}">
                                        <select name="{{ $field->name }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                            @foreach(App\Model\Admin\AdminUser::query()->where('status', App\Model\Admin\AdminUser::STATUS_ENABLE)->orderBy('name')->get(['id', 'name']) as $v)
                                                <option value="{{ $v->id }}" @if(isset($model) && $v->id == $model->{$field->name}) selected @endif>{{ $v->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @break
                                        @case('datetime')
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">{{ $field->form_name }}</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="{{ $field->name }}" class="layui-input" id="{{ $field->name }}" value="{{ $model->{$field->name} ?? $field->form_default_value }}">
                                            </div>
                                        </div>
                                        <script>
                                            addLoadEvent(function () {
                                                var laydate = layui.laydate;
                                                laydate.render({
                                                    elem: '#{{ $field->name }}',
                                                    type: 'datetime'
                                                });
                                            });
                                        </script>
                                        @break
                                        @case('date')
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">{{ $field->form_name }}</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="{{ $field->name }}" class="layui-input" id="{{ $field->name }}" value="{{ $model->{$field->name} ?? $field->form_default_value }}">
                                            </div>
                                        </div>
                                        <script>
                                            addLoadEvent(function () {
                                                var laydate = layui.laydate;
                                                laydate.render({
                                                    elem: '#{{ $field->name }}',
                                                });
                                            });
                                        </script>
                                        @break
                            @case('checkbox')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        @foreach(parseEntityFieldParams($field->form_params) as $v)
                                            <input type="checkbox" name="{{ $field->name }}[]" value="{{ $v[0] }}" title="{{ xssFilter($v[1]) }}" lay-skin="primary" @if((isset($model) && isChecked($v[0], $model->{$field->name})) || (!isset($model) && isChecked($v[0], $field->form_default_value))) checked @endif @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                        @endforeach
                                    </div>
                                </div>
                                @break
                            @case('option')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block">
                                        @foreach(parseEntityFieldParams($field->form_params) as $v)
                                            <input type="radio" name="{{ $field->name }}" value="{{ $v[0] }}" title="{{ xssFilter($v[1]) }}" @if((isset($model) && $v[0] == $model->{$field->name}) || (!isset($model) && $v[0] == $field->form_default_value) || $loop->first) checked @endif @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                        @endforeach
                                    </div>
                                </div>
                                @break
                            @case('select')
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block" style="width: 400px;z-index: {{99999 - ($field->order + $field->id)}}">
                                        <select name="{{ $field->name }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                        @foreach(parseEntityFieldParams($field->form_params) as $v)
                                            <option value="{{ $v[0] }}" @if((isset($model) && $v[0] == $model->{$field->name}) || (!isset($model) && $v[0] == $field->form_default_value)) selected @endif>{{ $v[1] }}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                                @break
                                @case('selectMulti')
                                    @if(!isset($selects_init))
                                        @php
                                            // select??????????????????????????? https://github.com/hnzzmsf/layui-formSelects
                                            $selects_init = true
                                        @endphp
                                        <link rel="stylesheet" type="text/css" href="/public/vendor/layui-v2.4.5/plugins/formSelects-v4.css"/>
                                        <!--<script type="text/javascript" src="/public/vendor/neditor/third-party/jquery-1.10.2.min.js"></script>-->
                                        <script type="text/javascript" src="/public/vendor/layui-v2.4.5/plugins/formSelects-v4.min.js"></script>
                                    @endif
                                    <div class="layui-form-item">
                                        <label class="layui-form-label">{{ $field->form_name }}</label>
                                        <div class="layui-input-block" style="width: 600px;z-index: {{99999 - ($field->order + $field->id)}}">
                                            <select xm-select-search xm-select="select-{{ $field->name }}" name="{{ $field->name }}" @if($field->is_required == \App\Model\Admin\EntityField::REQUIRED_ENABLE) required  lay-verify="required" @endif @if(isset($model) && $field->is_edit == \App\Model\Admin\EntityField::EDIT_DISABLE) disabled @endif>
                                                @foreach(parseEntityFieldParams($field->form_params) as $v)
                                                    @if ($field->type == 'unsignedInteger')
                                                        <option value="{{ $v[0] }}" @if((isset($model) && isCheckedByAnd($v[0], $model->{$field->name})) || (!isset($model) && isCheckedByAnd($v[0], $field->form_default_value))) selected @endif>{{ $v[1] }}</option>
                                                    @else
                                                        <option value="{{ $v[0] }}" @if((isset($model) && isChecked($v[0], $model->{$field->name})) || (!isset($model) && isChecked($v[0], $field->form_default_value))) selected @endif>{{ $v[1] }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <script>
                                        formSelects.render('select-{{ $field->name }}');
                                    </script>
                                @break
                            @case('inputTags')
                                @if(!isset($tagify_init))
                                    @php
                                        // https://github.com/yairEO/tagify
                                        $tagify_init = true
                                    @endphp
                                    <link rel="stylesheet" type="text/css" href="/public/vendor/tagify/tagify.css"/>
                                    <script type="text/javascript" src="/public/vendor/tagify/tagify.min.js"></script>
                                @endif
                                <div class="layui-form-item">
                                    <label class="layui-form-label">{{ $field->form_name }}</label>
                                    <div class="layui-input-block" style="">
                                        <input name="{{ $field->name }}" placeholder="??????????????????????????????TAB??????????????????" value="@if(isset($model)){{ \App\Repository\Admin\ContentRepository::tagNames($entity, $model->id) }},@endif">
                                    </div>
                                </div>
                                <script>
                                    var input = document.querySelector('input[name={{ $field->name }}]'),
                                        // init Tagify script on the above inputs
                                        tagify = new Tagify(input, {
                                            dropdown: {
                                                enabled: 1,
                                                maxItems: 50,
                                                highlightFirst: true
                                            }
                                        });

                                    // Chainable event listeners
                                    tagify.on('input', onInput);

                                    // on character(s) added/removed (user is typing/deleting)
                                    function onInput(e){
                                        var value = e.detail.value;
                                        tagify.settings.whitelist = [];
                                        tagify.dropdown.hide.call(tagify);
                                        $.ajax({
                                            url: "{{ route('admin::tag.list') }}" + "?page=1&limit=50" + "&name=" + value,
                                            method: "GET",
                                            dataType: "json",
                                            success: function (d) {
                                                if (d.code === 0 && d.count > 0) {
                                                    var data = [];
                                                    for (var j = 0; j < d.data.length; j++) {
                                                        data.push(d.data[j].name);
                                                    }
                                                    tagify.settings.whitelist = data;
                                                    tagify.dropdown.show.call(tagify, value);
                                                }
                                            }
                                        });
                                    }
                                </script>
                                @break

                        @endswitch
                    @endforeach
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit lay-filter="formAdminUser" id="submitBtn">??????</button>
                        <button type="reset" class="layui-btn layui-btn-primary">??????</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('foot_js')
@endsection

@section('js')

    <script>
        var form = layui.form;

        //????????????
        form.on('submit(formAdminUser)', function(data){
            window.onbeforeunload = null;
            window.form_submit = $('#submitBtn');
            form_submit.prop('disabled', true);
            $.ajax({
                url: data.form.action,
                data: data.field,
                success: function (result) {
                    if (result.code !== 0) {
                        form_submit.prop('disabled', false);
                        layer.msg(result.msg, {shift: 6});
                        return false;
                    }
                    layer.msg(result.msg, {icon: 1}, function () {
                        if (result.reload) {
                            location.reload();
                        }
                        if (result.redirect) {
                            location.href = result.redirect;
                        }
                    });
                }
            });

            return false;
        });
        window.jQuery = $;
        $('i.remove-image').unbind('click').on('click', function () {
            var previewArr = [];
            var container = $(this).parent().parent();

            $(this).parent().remove();
            container.find('img').each(function (i, v) {
                previewArr.push($(v).attr('src'));
            });
            var inputName = container.attr('id').replace('preview-image-', '');
            console.log(previewArr.join(','));
            $("input[name=" + inputName + ']').val(previewArr.join(','));
        });

        window.onbeforeunload = function (e) {
          e = e || window.event;
          // ??????IE8???Firefox 4???????????????
          if (e) {
            e.returnValue = '??????????????????????????????';
          }
          // Chrome, Safari, Firefox 4+, Opera 12+ , IE 9+
          return '??????????????????????????????';
        };
    </script>
    <script src="/public/vendor/zoom/transition.js"></script>
    <script src="/public/vendor/zoom/zoom.min.js"></script>
@endsection
