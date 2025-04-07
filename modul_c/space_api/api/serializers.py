# api/serializers.py

from rest_framework import serializers
from .models import User, LunarMission, SpaceFlight, LaunchSite, LandingSite, Spacecraft, CrewMember

class UserSerializer(serializers.ModelSerializer):
    class Meta:
        model = User
        fields = ['id', 'first_name', 'last_name', 'patronymic', 'email', 'password', 'birth_date']
        extra_kwargs = {'password': {'write_only': True}}

    def create(self, validated_data):
        user = User(**validated_data)
        user.set_password(validated_data['password'])
        user.save()
        return user

class CrewMemberSerializer(serializers.ModelSerializer):
    class Meta:
        model = CrewMember
        fields = ['name', 'role']

class SpacecraftSerializer(serializers.ModelSerializer):
    crew = CrewMemberSerializer(many=True)

    class Meta:
        model = Spacecraft
        fields = ['command_module', 'lunar_module', 'crew']

class LaunchSiteSerializer(serializers.ModelSerializer):
    class Meta:
        model = LaunchSite
        fields = ['name', 'location']  # location будет вложенным полем

class LandingSiteSerializer(serializers.ModelSerializer):
    class Meta:
        model = LandingSite
        fields = ['name', 'coordinates']  # coordinates будет вложенным полем

class LunarMissionSerializer(serializers.ModelSerializer):
    launch_details = serializers.DictField()  # Используем DictField для обработки вложенных данных
    landing_details = serializers.DictField()  # Используем DictField для обработки вложенных данных
    spacecraft = SpacecraftSerializer()  # Используем SpacecraftSerializer для обработки вложенных данных

    class Meta:
        model = LunarMission
        fields = ['name', 'launch_details', 'landing_details', 'spacecraft']

    def validate_name(self, value):
            if not value or not value[0].isupper():
                raise serializers.ValidationError("Название миссии должно начинаться с заглавной буквы.")
            return value

    def create(self, validated_data):
        # Извлечение вложенных данных
        launch_details = validated_data.pop('launch_details')
        landing_details = validated_data.pop('landing_details')
        spacecraft_data = validated_data.pop('spacecraft')

        # Создание объектов LaunchSite и LandingSite
        launch_site_data = launch_details.pop('launch_site')
        landing_site_data = landing_details.pop('landing_site')

        # Извлечение широты и долготы
        launch_latitude = launch_site_data['location']['latitude']
        launch_longitude = launch_site_data['location']['longitude']
        landing_latitude = landing_site_data['coordinates']['latitude']
        landing_longitude = landing_site_data['coordinates']['longitude']

        launch_site = LaunchSite.objects.create(
            name=launch_site_data['name'],
            latitude=launch_latitude,
            longitude=launch_longitude
        )
        landing_site = LandingSite.objects.create(
            name=landing_site_data['name'],
            latitude=landing_latitude,
            longitude=landing_longitude
        )

        # Создание объекта Spacecraft
        spacecraft = Spacecraft.objects.create(
            command_module=spacecraft_data['command_module'],
            lunar_module=spacecraft_data['lunar_module']
        )

        # Создание членов экипажа
        crew_data = spacecraft_data['crew']
        for crew_member_data in crew_data:
            crew_member = CrewMember.objects.create(**crew_member_data)
            spacecraft.crew.add(crew_member)  # Добавление члена экипажа в ManyToMany поле

        # Создание объекта LunarMission
        mission = LunarMission.objects.create(
            name=validated_data['name'],
            launch_date=launch_details['launch_date'],
            landing_date=landing_details['landing_date'],
            launch_site=launch_site,
            landing_site=landing_site,
            spacecraft=spacecraft
        )

        return mission

class SpaceFlightSerializer(serializers.ModelSerializer):
    class Meta:
        model = SpaceFlight
        fields = ['flight_number', 'destination', 'launch_date', 'seats_available']

    def validate_destination(self, value):
        if not value or not value[0].isupper():  # Проверяем первый символ
            raise serializers.ValidationError("Назначение должно начинаться с заглавной буквы.")
        return value