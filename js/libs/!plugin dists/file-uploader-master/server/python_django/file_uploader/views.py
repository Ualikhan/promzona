"""
@author: Ferdinand E. Silva
@email: ferdinandsilva@ferdinandsilva.com
@website: http://ferdinandsilva.com
"""
from django.shortcuts import render_to_response, HttpResponse
from django.template import RequestContext
from django.views.decorators.csrf import csrf_exempt

from file_uploader import settings
from file_uploader import qqFileUploader


def index(request):
    return render_to_response('demo.htm', context_instance=RequestContext(request))


@csrf_exempt
def upload(request):
    allowedExtension = [".jpg", ".png", ".ico", ".*"]
    sizeLimit = 1024000
    uploader = qqFileUploader(allowedExtension, sizeLimit)
    return HttpResponse(uploader.handleUpload(request, settings.MEDIA_ROOT + "upload/"))
