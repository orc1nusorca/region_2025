from rest_framework import viewsets, permissions, status
from rest_framework.response import Response
from rest_framework.views import APIView
from django.contrib.auth import get_user_model
from rest_framework.exceptions import AuthenticationFailed
from rest_framework.exceptions import ValidationError
from django.core.files.storage import default_storage
from PIL import Image, ImageDraw, ImageFont
import io
import os
from django.http import FileResponse
from rest_framework_simplejwt.views import TokenObtainPairView
from .models import User, LunarMission, SpaceFlight, LaunchSite, LandingSite, CrewMember
from .serializers import UserSerializer, LunarMissionSerializer, SpaceFlightSerializer

class CustomTokenObtainPairView(TokenObtainPairView):
    def post(self, request, *args, **kwargs):
        try:
            response = super().post(request, *args, **kwargs)
            user = get_user_model().objects.get(email=request.data['email'])
            return Response({
                'data': {
                    'user': {
                        'id': user.id,
                        'name': f"{user.first_name} {user.patronymic} {user.last_name}",
                        'birth_date': user.birth_date,
                        'email': user.email
                    },
                    'token': response.data['access'],
                }
            })
        except AuthenticationFailed:
            return Response({"message": "Login failed"}, status=status.HTTP_403_FORBIDDEN)
        except get_user_model().DoesNotExist:
            return Response({"message": "Login failed"}, status=status.HTTP_403_FORBIDDEN)

class LogoutView(APIView):
    permission_classes = [permissions.IsAuthenticated]

    def get(self, request):
        return Response(status=status.HTTP_204_NO_CONTENT)

class UserRegistrationView(APIView):
    permission_classes = [permissions.AllowAny]

    def post(self, request):
        serializer = UserSerializer(data=request.data)
        if serializer.is_valid():
            user = serializer.save()
            return Response({
                "data": {
                    "user": {
                        "name": f"{user.last_name} {user.first_name} {user.patronymic}",
                        "email": user.email
                    },
                    "code": 201,
                    "message": "Пользователь создан"
                }
            }, status=status.HTTP_201_CREATED)
        return Response({
            "error": {
                "code": 422,
                "message": "Validation error",
                "errors": serializer.errors
            }
        }, status=status.HTTP_422_UNPROCESSABLE_ENTITY)

class GagarinFlightInfoView(APIView):
    permission_classes = [permissions.IsAuthenticated]

    def get(self, request):
        data = {
            "data": [
                {
                    "mission": {
                        "name": "Восток 1",
                        "launch_details": {
                            "launch_date": "1961-04-12",
                            "launch_site": {
                                "name": "Космодром Байконур",
                                "location": {
                                    "latitude": "45.9650000",
                                    "longitude": "63.3050000"
                                }
                            }
                        },
                        "landing": {
                            "date": "1961-04-12",
                            "site": {
                                "name": "Смеловка",
                                "country": "СССР",
                                "coordinates": {
                                    "latitude": "51.2700000",
                                    "longitude": "45.9970000"
                                }
                            },
                            "details": {
                                "parachute_landing": True,
                                "impact_velocity_mps": 7
                            }
                        },
                        "cosmonaut": {
                            "name": "Юрий Гагарин",
                            "birthdate": "1934-03-09",
                            "rank": "Старший лейтенант",
                            "bio": {
                                "early_life": "Родился в Клушино, Россия.",
                                "career": "Отобран в отряд космонавтов в 1960 году...",
                                "post_flight": "Стал международным героем."
                            }
                        }
                    }
                }
            ]
        }
        return Response(data, status=status.HTTP_200_OK)
    

class LunarMissionInfoView(APIView):
    permission_classes = [permissions.IsAuthenticated]

    def get(self, request):
        missions_from_db = LunarMission.objects.all()
        db_missions = []

        for mission in missions_from_db:
            db_missions.append({
                "mission": {
                    "name": mission.name,
                    "launch_details": {
                        "launch_date": mission.launch_date.isoformat(),
                        "launch_site": {
                            "name": mission.launch_site.name,
                            "location": {
                                "latitude": mission.launch_site.latitude,                                "longitude": mission.launch_site.longitude
                            }
                        }
                    },
                    "landing_details": {
                        "landing_date": mission.landing_date.isoformat(),
                        "landing_site": {
                            "name": mission.landing_site.name,
                            "coordinates": {
                                "latitude": mission.landing_site.latitude,
                                "longitude": mission.landing_site.longitude
                            }
                        }
                    },
                    "spacecraft": {
                        "command_module": mission.spacecraft.command_module,
                        "lunar_module": mission.spacecraft.lunar_module,
                        "crew": [
                            {
                                "name": crew_member.name,
                                "role": crew_member.role
                            } for crew_member in mission.spacecraft.crew.all()
                        ]
                    }
                }
            })

        return Response({"missions": db_missions}, status=status.HTTP_200_OK)

    def post(self, request):
        mission_data = request.data.get('mission')
        if not mission_data:
            return Response({"error": "Mission data is required."}, status=status.HTTP_400_BAD_REQUEST)

        serializer = LunarMissionSerializer(data=mission_data)
        if serializer.is_valid():
            mission = serializer.save()
            return Response({
                "data": {
                    "code": 201,
                    "message": "Миссия добавлена"
                }
            }, status=status.HTTP_201_CREATED)

        return Response({
            "error": {
                "code": 422,
                "message": "Validation error",
                "errors": serializer.errors
            }
        }, status=status.HTTP_422_UNPROCESSABLE_ENTITY)

    def delete(self, request, mission_id):
        try:
            mission = LunarMission.objects.get(id=mission_id)
            mission.delete()
            return Response(status=status.HTTP_204_NO_CONTENT)
        except LunarMission.DoesNotExist:
            return Response({
                "message": "Not found",
                "code": 404
            }, status=status.HTTP_404_NOT_FOUND)

    def patch(self, request, mission_id):
        try:
            mission = LunarMission.objects.get(id=mission_id)
            mission_data = request.data.get('mission', {})

            # Обновление полей миссии
            mission.name = mission_data.get('name', mission.name)

            # Обновление launch_details
            launch_details = mission_data.get('launch_details', {})
            if launch_details:
                mission.launch_date = launch_details.get('launch_date', mission.launch_date)
                launch_site_data = launch_details.get('launch_site', {})
                if launch_site_data:
                    mission.launch_site.name = launch_site_data.get('name', mission.launch_site.name)
                    location = launch_site_data.get('location', {})
                    mission.launch_site.latitude = location.get('latitude', mission.launch_site.latitude)
                    mission.launch_site.longitude = location.get('longitude', mission.launch_site.longitude)
                    mission.launch_site.save()

            # Обновление landing_details
            landing_details = mission_data.get('landing_details', {})
            if landing_details:
                mission.landing_date = landing_details.get('landing_date', mission.landing_date)
                landing_site_data = landing_details.get('landing_site', {})
                if landing_site_data:
                    mission.landing_site.name = landing_site_data.get('name', mission.landing_site.name)
                    coordinates = landing_site_data.get('coordinates', {})
                    mission.landing_site.latitude = coordinates.get('latitude', mission.landing_site.latitude)
                    mission.landing_site.longitude = coordinates.get('longitude', mission.landing_site.longitude)
                    mission.landing_site.save()

            # Обновление spacecraft
            spacecraft_data = mission_data.get('spacecraft', {})
            if spacecraft_data:
                mission.spacecraft.command_module = spacecraft_data.get('command_module', mission.spacecraft.command_module)
                mission.spacecraft.lunar_module = spacecraft_data.get('lunar_module', mission.spacecraft.lunar_module)
                mission.spacecraft.save()

                # Обновление членов экипажа
                crew_data = spacecraft_data.get('crew', [])
                mission.spacecraft.crew.clear()
                for crew_member in crew_data:
                    crew_member_obj = CrewMember.objects.create(**crew_member)
                    mission.spacecraft.crew.add(crew_member_obj)

            mission.save()
            return Response({
                "data": {
                    "code": 200,
                    "message": "Миссия обновлена"
                }
            }, status=status.HTTP_200_OK)

        except LunarMission.DoesNotExist:
            return Response({
                "message": "Not found",
                "code": 404
            }, status=status.HTTP_404_NOT_FOUND)
        except Exception as e:
            return Response({
                "error": {
                    "code": 500,
                    "message": "Internal server error",
                    "errors": str(e)
                }
            }, status=status.HTTP_500_INTERNAL_SERVER_ERROR)

# Пример для SpaceFlightCreateView
class SpaceFlightCreateView(APIView):
    permission_classes = [permissions.IsAuthenticated]
    def post(self, request):
        serializer = SpaceFlightSerializer(data=request.data)
        if serializer.is_valid():
            serializer.save()
            return Response({
                "data": {
                    "code": 201,
                    "message": "Космический полет создан"
                }
            }, status=status.HTTP_201_CREATED)
        return Response({
            "error": {
                "code": 422,
                "message": "Validation error",
                "errors": serializer.errors
            }
        }, status=status.HTTP_422_UNPROCESSABLE_ENTITY)

    def get(self, request):
        flights = SpaceFlight.objects.all()
        serializer = SpaceFlightSerializer(flights, many=True)
        return Response({
            "data": serializer.data
        }, status=status.HTTP_200_OK)

# Пример для BookFlightView
class BookFlightView(APIView):
    permission_classes = [permissions.IsAuthenticated]
    def post(self, request):
        flight_number = request.data.get('flight_number')
        
        try:
            flight = SpaceFlight.objects.get(flight_number=flight_number)
            if flight.seats_available > 0:
                flight.seats_available -= 1  # Уменьшаем количество доступных мест
                flight.save()  # Сохраняем изменения
                return Response({
                    "data": {
                        "code": 201,
                        "message": "Рейс забронирован"
                    }
                }, status=status.HTTP_201_CREATED)
            else:
                return Response({
                    "error": {
                        "code": 400,
                        "message": "Нет доступных мест"
                    }
                }, status=status.HTTP_400_BAD_REQUEST)
        except SpaceFlight.DoesNotExist:
            return Response({
                "message": "Not found",
                "code": 404
            }, status=status.HTTP_404_NOT_FOUND)

# Пример для SearchView
class SearchView(APIView):
    permission_classes = [permissions.IsAuthenticated]
    def get(self, request):
        query = request.query_params.get('query', None)
        if not query:
            return Response({
                "error": {
                    "code": 400,
                    "message": "Query parameter is required."
                }
            }, status=status.HTTP_400_BAD_REQUEST)

        # Поиск по миссиям
        missions = LunarMission.objects.filter(name__icontains=query)
        results = []

        for mission in missions:
            crew = mission.spacecraft.crew.all()  # Получение членов экипажа
            crew_data = [{"name": member.name, "role": member.role} for member in crew]

            results.append({
                "type": "Миссия",
                "name": mission.name,
                "launch_date": mission.launch_date,
                "landing_date": mission.landing_date,
                "crew": crew_data,
                "landing_site": mission.landing_site.name
            })

        return Response({"data": results}, status=status.HTTP_200_OK)

# Пример для LunarWatermarkView
class LunarWatermarkView(APIView):
    permission_classes = [permissions.IsAuthenticated]

    def post(self, request):
        fileimage = request.FILES.get('fileimage')
        message = request.data.get('message')

        # Проверка обязательных полей
        if not fileimage or not message:
            return Response({
                "error": {
                    "code": 400,
                    "message": "fileimage and message are required."
                }
            }, status=status.HTTP_400_BAD_REQUEST)

        # Проверка длины сообщения
        if len(message) < 10 or len(message) > 20:
            return Response({
                "error": {
                    "code": 400,
                    "message": "Message must be between 10 and 20 characters."
                }
            }, status=status.HTTP_400_BAD_REQUEST)

        # Открытие изображения
        try:
            image = Image.open(fileimage).convert("RGBA")  # Конвертация в RGBA для поддержки прозрачности
        except Exception as e:
            return Response({
                "error": {
                    "code": 400,
                    "message": "Invalid image file."
                }
            }, status=status.HTTP_400_BAD_REQUEST)

        # Добавление водяного знака
        draw = ImageDraw.Draw(image)
        font_path = os.path.join(os.path.dirname(__file__), 'assets', 'Roboto_Condensed-Bold.ttf')

        # Загрузка шрифта
        try:
            font = ImageFont.truetype(font_path, 40)  # Укажите путь к вашему шрифту и размер
        except IOError:
            return Response({
                "error": {
                    "code": 400,
                    "message": "Font file not found."
                }
            }, status=status.HTTP_400_BAD_REQUEST)

        # Получение размеров текста
        text_bbox = draw.textbbox((0, 0), message, font=font)  # Получаем границы текста
        text_width = text_bbox[2] - text_bbox[0]  # Ширина текста
        text_height = text_bbox[3] - text_bbox[1]  # Высота текста

        # Определение позиции для текста
        width, height = image.size
        position = (width - text_width - 10, height - text_height - 10)  # Позиция в правом нижнем углу

        # Рисуем текст на изображении
        draw.text(position, message, fill=(255, 255, 255, 255), font=font)  # Белый текст с прозрачностью

        # Сохранение изображения в памяти
        img_byte_arr = io.BytesIO()
        image.save(img_byte_arr, format='PNG')
        img_byte_arr.seek(0)

        # Возврат изображения в ответе
        response = FileResponse(img_byte_arr, content_type='image/png')
        response['Content-Disposition'] = 'attachment; filename="watermarked_image.png"'
        return response