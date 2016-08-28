@extends('master')

@section('title', 'TOP')

@section('content')

<h2>はじめに</h2>

画像からバーコードを読み込み、JSONで結果を返します。

<h2>サンプル</h2>

<div class="panel panel-default sample-curl">
    <div class="panel-heading"><h3 class="h3">cURL</h3></div>
    <div class="panel-body">
        curl -X POST -F file=@image.jpg {server_url}/v1/detect
    </div>
</div>    

<div class="panel panel-default sample-form">
    <div class="panel-heading"><h3>フォーム</h3></div>
    <div class="panel-body form-horizontal">

        <form method='POST' action="/v1/detect" enctype='multipart/form-data'>
            <div class="form-group">
                <label for='file' class="col-sm-2 control-label">Image file</label>
                <input type="file" name="file" />

            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <input type='submit' class="btn btn-default" />
                </div>
            </div>
        </form>
    </div>
</div>

<h2>JSON</h2>
<div class="panel panel-default">
    <div class="panel-heading"><h3>成功</h3></div>
    <div class="panel-body form-horizontal">
        <pre>
{
    result: true,
    message: "Barcode find.",
    barcode: {
        type: "EAN-XX",
        value: "4900000000000"
    }
}
        </pre>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><h3>失敗</h3></div>
    <div class="panel-body form-horizontal">
        <pre>
{
    result: false,
    message: "Error Message."
}
        </pre>
    </div>
</div>



<h2>Message</h2>
<ul>
    <li>File not found.</li>
    POSTでファイルが送信されていない
    <li>File valid error.</li>
    アップロードに失敗した
    <li>File type is not image.</li>
    画像以外のファイルがアップロードされた
    <li>File size is too large.</li>
    アップロードされたファイルのサイズが大きい(最大2MB)
    <li>Barcode not found.</li>
    バーコードが見つからなかった
    <li>Barcode find.</li>
    バーコードが見つかった
</ul>



<h2>リンク</h2>
<ul>
    <li><a href="http://zbar.sourceforge.net/">ZBar bar code reader</a></li>
    <li><a href="https://github.com/yyano/barcode-detector-api">GitHub - yyano/barcode-detector-api</a></li>
</ul>

@endsection