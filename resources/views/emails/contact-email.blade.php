<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0;">
    <table cellspacing="0" cellpadding="0" width="100%" bgcolor="#fff" style="padding: 5px 10px 0px 10px;">
        <tr>
            <td align="left" valign="top">
                <div class="content" style="margin: 0; padding: 0;">
                    {!! $data['content'] !!}
                </div>
            </td>
        </tr> 
    </table>
   
    <table cellspacing="0" cellpadding="0" width="100%" bgcolor="#fff" style="padding: 0 10px 0; font-family: Google Sans,Roboto,sans-serif; font-size: 13px; color: #646464;">   
    @if($data['signature_image'] || $data['signature_text'])
        <tr>
            <td>
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        @if($data['signature_image'] && file_exists(public_path('images/signature/'.$data['signature_image'])))
                        <td align="left" valign="middle" width="7%" style="min-width: 90px; padding-right: 5px; border-right: 1px solid #000;">
                            <figure style="margin: 0;">
                                <img style="width: 100%;" src="{{asset('images/signature/'.$data['signature_image'])}}" alt="">
                            </figure>
                        </td>
                        @endif
                        @if($data['signature_text'])
                        <td align="left" valign="middle" width="80%" style="padding-left: 5px; margin: 0;">
                            {!! $data['signature_text'] !!}
                        </td>
                        @endif
                    </tr>
                </table>
            </td>
        </tr>
    @endif
        <tr>
            <td align="left" valign="middle" style="padding-top: 10px;">
                <p style="margin: 0; line-height: 1.5;">This email and its attachments may contain confidential information intended only for the use of the person(s) named above. If you are not the intended recipient, you are hereby advised that any disclosure, copying, distribution or the taking of any action on the contents of this information is prohibited. If you've received this email in error, please notify the sender.</p>
            </td>
        </tr>
    </table>
</body>
</html>
