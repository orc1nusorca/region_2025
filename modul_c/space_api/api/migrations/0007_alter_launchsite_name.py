# Generated by Django 4.2.19 on 2025-02-09 14:43

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('api', '0006_crewmember_landingsite_launchsite_and_more'),
    ]

    operations = [
        migrations.AlterField(
            model_name='launchsite',
            name='name',
            field=models.CharField(max_length=255),
        ),
    ]
